**Install** 

    composer require --dev motniemtin/auto
    
**Test code**    

    <?php
    require_once  "vendor/autoload.php";
    use Motniemtin\Auto\Auto;
    
    $auto=new Auto(1,"cookie.ck");
    echo $auto->Get('sample.com');
    $string="123456789123456789123456789<div class=\"divclass\">div content 1</div><div class=\"divclass\">div content 2</div>";
    echo $auto->SCodeOne("1","9",$string)."\n";
    print_r($auto->SCodeMulti("1","9",$string));
    echo $auto->STagOne("<div class=\"divclass\"",$string)."\n";
    print_r($auto->STagMulti("<div class=\"divclass\"",$string));

# Viettel
