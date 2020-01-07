<!DOCTYPE html>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function test($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if ($_POST){
  $fp = fopen(test($_POST['file']).'.json', 'w');
  $data = array("category" => test($_POST['category']), "segments" => array());
  for ($i=0; $i < count($_POST['in']); $i++){
    if ($_POST['in'][$i] != ''){
      $data['segments'][] = array("in" => floatval(test($_POST['in'][$i])), "out" => floatval($_POST['out'][$i]));
    }
  }
  fwrite($fp, json_encode($data));
  fclose($fp);
}

function strip_name(&$item){
  $item = substr($item, 0, strrpos($item, '.'));
}

$all = glob('video/*/*.webm');
$processed = glob('video/*/*.json');
array_walk($all, "strip_name");
array_walk($processed, "strip_name");
$todo = array_diff($all, $processed);

if (count($todo) == 0){
  print("Hotovo");die();
}

$current = $todo[array_rand($todo)];
?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Film o filmu</title>
    
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    
  </head>
  <body>
  
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="#">DFSKZ, zbývá <?=count($todo); ?> videí</a>
        </div>
    </nav>
    
    <div class="container">
      <div class="col-md-8">
        <video width="100%" id="video" controls>
          <source src="<?=$current;?>.webm"> 
        </video>
        <div id="lines">
          <div class="linewrap"><div style="height:2px; left:0%; right:0%; background: gray;"></div></div>
          <div class="linewrap"><div id="line1" style="left:0%; right:100%; background: red;"></div></div>
        </div>
      </div>
      
      <div class="col-md-4">
        <h3>Instrukce</h3>
        <p>Prosím, pro každé video označte do jaké kategorie patří. Pusťte si ho a poté označte vždy zajímavé úseky pomocí tlačítka <span class="glyphicon glyphicon-step-forward"></span> pro začátek a <span class="glyphicon glyphicon-step-backward"></span> pro konec úseku. Více než jeden úsek přidáte ikonkou <span class="glyphicon glyphicon-plus"></span>. Přehled všech vybraných úseků je zobrazen červenými čárami pod videem.</p>
        <form method="post" id="form">
          <input type="hidden" name="file" value="<?=$current;?>">
          Kategorie:
          <select name="category">
            <option value="0">Odpad</option>
            <option value="1">Hlasovky, Casting</option>
            <option value="2">Kostýmy, Masky</option>
            <option value="3">Za kamerou</option>
            <option value="4">Volný čas</option>
            <option value="5">Vedoucí blbnou</option>
            <option value="6">Jiné...</option>
          </select>
      
          <table class="table" id="table">
            <thead>
            <tr><th></th><th>Začátek</th><th>Konec</th><th></th></tr>
            </thead>
            <tbody>
            <tr>
              <td><span class="btn glyphicon glyphicon-step-forward" onclick="setin(1)"></span></td>
              <td><input name="in[]" id="in1" /></td>
              <td><input name="out[]" id="out1" /></td>
              <td><span class="btn glyphicon glyphicon-step-backward" onclick="setout(1)"></span></td>
            </tr>
            </tbody>
          </table>
          <span class="btn glyphicon glyphicon-plus" onclick="next()"></span>
          <input type="submit" value="Odeslat" />
      </div>

    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery.min.js"></script>
    <script type="text/javascript">
      function setin(field){
       time = $("#video")[0].currentTime;
       $('#in'+field).val(time);
       $('#line'+field).css('left',(time/dur*100)+'%');
      }
      
      function setout(field){
       time = $("#video")[0].currentTime;
       $('#out'+field).val(time);
       $('#line'+field).css('right',((dur-time)/dur*100)+'%');
      }
      
      var dur = $("#video")[0].duration;
      $("#video").bind('durationchange', function(){
       dur = $("#video")[0].duration;
      });
      
      var fields = 1;
      function next(){
        fields = fields + 1;
        $("#table tbody").append('<tr><td><span class="btn glyphicon glyphicon-step-forward" onclick="setin('+fields+')"></span></td><td><input name="in[]" id="in'+fields+'" / ></td><td><input name="out[]" id="out'+fields+'" / ></td><td><span class="btn glyphicon glyphicon-step-backward" onclick="setout('+fields+')"></span></td></tr>');
        $("#lines").append('<div class="linewrap"><div id="line'+fields+'" style="left:0%; right:100%; background: red;"></div></div>');
      }
      
      $("#form").bind('submit', function(){
        for (i=1;i<=fields;i++){
          if (Number($('#out'+i).val()) < Number($("#in"+i).val())){
            alert("Prohozené hodnoty Začátek - Konec na řádku "+i);
            return false;
          }
          if ($('#in'+i).val() != '' && $('#out'+i).val() == '' || $('#out'+i).val() != '' && $('#in'+i).val() == ''){
            alert("Je vyplněn jen Začátek nebo Konec na řádku "+i);
            return false;
          }
        }
        if ($('select').val() != 0 && $('#in1').val() == '' && $('#out1').val() == ''){
          alert("Nebyl vybrán žádný kus videa!");
          return false;
        }
        return true;
      })
      
    </script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
