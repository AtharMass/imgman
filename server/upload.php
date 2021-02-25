<?php

require_once '../server/ImageM.php';

$uploadDirectory = "uploads/";


//Check if button submitted
if (isset($_POST['submit']) ) {

    $file = $_FILES['upload'];

    try{ 

        //Create new object ImageM
        $im = new ImageM($file,$uploadDirectory);

        [$status, $response] = $im->main_image_manipulation();

        if($status) {
            $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Result Page</title>

                <link rel="stylesheet" href="/css/result.css">
            </head>
            <body>
            <div class="box">
                <div class="img-container">
                    <img src="../'. "server/" .$uploadDirectory  . basename( $file['name']).'" alt="">
                </div>
                <div class="details">';

            foreach($response as $res){
                if(($res['color']["r"]==0 || $res['color']["g"]==0 || $res['color']["b"]==0) || ( ($res['color']["r"]==$res['color']["b"] 
                    || $res['color']["r"]==$res['color']["g"]) && $res['color']["r"]<=152) || ($res['color']["r"]<152 && $res['color']["g"]<152 && $res['color']["b"]<152)) {
                    $text_color= 'white';
                }
                else {
                    $text_color= 'black';
                }

                $html .=  
                    '<div class="d-box">
                        <div class="percent" style="color: '.$text_color.';background-color:rgb('.$res['color']["r"] .', '.$res['color']["g"].', '.$res['color']["b"].');">
                            '.$res['percent'].' %
                        </div>
                        <div class="rgb">
                            R:'.$res['color']["r"] .' G:'.$res['color']["g"] .' B:'.$res['color']["b"].'
                        </div>
                    </div>';
            }
            
            
                    
            $html  .= '</div>
            </div></body>
            </html>';

            echo $html;
              
        } else {
            echo $response;
        }

    }catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    }
   
}else{
    echo '<h1 style="text-align: center;margin: 4%;">Are you looking for something?</h1>';
}
