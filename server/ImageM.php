<?php
class ImageM {

    // Properties
    private $file;
    private $uploadDirectory;
    private $fileExtensionsAllowed = ['png'];
    private $errors = [];

    //Constructor
    function __construct(array $file , string $uploadDirectory) {
        $this->set_upload_directory($uploadDirectory);
        $this->set_file($file);
    }

    // Set Methods
    public function set_file(array $file) {
        if(isset($_FILES['upload']) && $_FILES['upload']['size'] != 0 && $_FILES['upload']['error'] == 0)
            $this->file = $file;
        else
            throw new Exception('This is not file, Please send a file.');
    }
    public function set_upload_directory(string $uploadDirectory) {
        if(!empty($uploadDirectory)) 
            $this->uploadDirectory = $uploadDirectory;
        else
            throw new Exception('This upload directory is empty.');
    }

    // Get Methods
    public function get_file() : array {
        return $this->file ;
    }
    public function get_upload_directory() : string {
        return $this->uploadDirectory;
    }
    public function get_file_extensions_allowed() : array {
        return $this->fileExtensionsAllowed;
    }

    //Function for checking the allowed file extensions (file format is png in this solution)
    private function check_if_file_extensions_allowed(string $fileExtension)  {
        if (!in_array($fileExtension , $this->get_file_extensions_allowed() ) ) {
            throw new Exception("This file extension is not allowed. Please upload 'png|PNG' file");
        }
    }
    //Function for finding colors in the image and the total
    private function calculate_image_colors_counts(int $total, array $arr , $imresource , int $width , int $height) : array {

        for($x = 0; $x < $width; $x++) {
            for($y = 0; $y < $height; $y++) {
                // pixel color at (x, y)
                $color = imagecolorat( $imresource, $x, $y);
                
                if(isset($arr[$color])) {
                    $arr[$color] += 1;
                }else {
                    $arr[$color] = 1;
                }

                $total++;
            }
        }

        arsort($arr);

        return [$total, $arr];
    }
    //Function for displaying the five most popular RGB colors in an image
    private function popular_RGB(int $total, array $arr, int $limit) : array {
        $i = 0;
        $response = [];

        foreach ( $arr as $key => $val) {
    
            // from php official documentation
            $r = ($key >> 16) & 255;
            $g = ($key >> 8) & 255;
            $b = $key & 255;

            array_push($response, [
                "color"      => [
                    "r" => $r,
                    "g" => $g,
                    "b" => $b,
                ],
                "percent"    => number_format( ($val/$total * 100), 2),
            ]);

            $i++;
            if($i > $limit - 1) break;
        }
    
        return $response;
       
    }

    private function image_manipulation(string $fileName, string $fileTmpName, string  $uploadPath, int $limit) : array {
        
            $didUpload = move_uploaded_file($fileTmpName, $uploadPath);

            if ($didUpload) {
                // echo '<h1 style="text-align: center;margin: 4%;">The file "' . basename($fileName) . '" has been uploaded </h1>';

                $furl = __DIR__.'/uploads//'.$fileName;

                [$width, $height, $type, $attr] = getimagesize($furl);

                $memory_limit = $width * $height * 4;
                $memory_limit_mb = ($memory_limit / 1000000 ) + 128; 
                
                ini_set('memory_limit', $memory_limit_mb.'M');

                $imresource = imagecreatefrompng($furl);
                $arr = [];
                $total = 0;

                //Store array values into variables
                [$total, $arr] = $this->calculate_image_colors_counts($total ,$arr ,  $imresource , $width , $height);


                $response = $this->popular_RGB($total ,$arr, $limit);
                return [true, $response];

            }else{
                return [false, "Error uploading the image."];
            }
    }

    public function main_image_manipulation(int $limit = 5) : array{
        $fileName = $this->file['name'];
        $fileType = $this->file['type'];
        $fileTmpName = $this->file['tmp_name'];
        $file_parts = explode('.', $fileName);
        $fileExtension = strtolower(end($file_parts));

        $uploadPath =  __DIR__ . "/" . $this->get_upload_directory() . basename($this->file['name']); 

        $this->check_if_file_extensions_allowed( $fileExtension);

        return $this->image_manipulation( $fileName,  $fileTmpName,  $uploadPath, $limit);
    }

}
