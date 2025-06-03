<?php
/*
<<<<<<< HEAD
 * PHP QR Code encoder - QR image generator
 * Uses PHP GD2 for outputting QR codes as images
 * 
 * Requires PHP GD extension enabled (extension=gd)
 * 
 * Copyright (C) 2010 Dominik Dzienia
 * Distributed under LGPL 3
 */

define('QR_IMAGE', true);

class QRimage {

    /**
     * Output QR code as PNG image
     *
     * @param array $frame The QR code frame matrix (array of strings of '1'/'0')
     * @param string|false $filename Output file path or false to output to browser
     * @param int $pixelPerPoint Size of each QR code module in pixels
     * @param int $outerFrame Width of the white frame (quiet zone) around QR code (in modules)
     * @param bool $saveandprint Save to file and output or just save
     */
    public static function png($frame, $filename = false, $pixelPerPoint = 4, $outerFrame = 4, $saveandprint = false) 
    {
        $image = self::image($frame, $pixelPerPoint, $outerFrame);

        if ($filename === false) {
            // Output directly to browser
            header("Content-Type: image/png");
            imagepng($image);
        } else {
            if ($saveandprint === true) {
                // Save to file and output to browser
                imagepng($image, $filename);
                header("Content-Type: image/png");
                imagepng($image);
            } else {
                // Just save to file
                imagepng($image, $filename);
            }
        }

        imagedestroy($image);
    }

    /**
     * Output QR code as JPG image
     *
     * @param array $frame The QR code frame matrix
     * @param string|false $filename Output file path or false to output to browser
     * @param int $pixelPerPoint Module size in pixels
     * @param int $outerFrame Quiet zone size in modules
     * @param int $quality JPEG quality (0-100)
     */
    public static function jpg($frame, $filename = false, $pixelPerPoint = 8, $outerFrame = 4, $quality = 85) 
    {
        $image = self::image($frame, $pixelPerPoint, $outerFrame);

        if ($filename === false) {
            header("Content-Type: image/jpeg");
            imagejpeg($image, null, $quality);
        } else {
            imagejpeg($image, $filename, $quality);
        }

        imagedestroy($image);
    }

    /**
     * Create GD image resource from QR code frame matrix
     *
     * @param array $frame QR code frame matrix (array of strings)
     * @param int $pixelPerPoint Module size in pixels
     * @param int $outerFrame Quiet zone size in modules
     * @return resource GD image resource
     */
    private static function image($frame, $pixelPerPoint = 4, $outerFrame = 4) 
    {
        $height = count($frame);
        $width = strlen($frame[0]);

        $imgWidth = $width + 2 * $outerFrame;
        $imgHeight = $height + 2 * $outerFrame;

        // Create base image (1 pixel per module + quiet zone)
        $baseImage = imagecreate($imgWidth, $imgHeight);

        // Colors: white and black
        $white = imagecolorallocate($baseImage, 255, 255, 255);
        $black = imagecolorallocate($baseImage, 0, 0, 0);

        // Fill background with white
        imagefill($baseImage, 0, 0, $white);

        // Draw the QR code modules (black squares)
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($frame[$y][$x] === '1') {
                    imagesetpixel($baseImage, $x + $outerFrame, $y + $outerFrame, $black);
                }
            }
        }

        // Scale image up by $pixelPerPoint to get bigger QR code
        $targetImage = imagecreatetruecolor($imgWidth * $pixelPerPoint, $imgHeight * $pixelPerPoint);

        // Preserve transparency for PNGs if needed (optional)
        imagefill($targetImage, 0, 0, $white);

        imagecopyresized(
            $targetImage, $baseImage, 
            0, 0, 0, 0, 
            $imgWidth * $pixelPerPoint, $imgHeight * $pixelPerPoint, 
            $imgWidth, $imgHeight
        );

        imagedestroy($baseImage);

        return $targetImage;
    }
}
=======
 * PHP QR Code encoder
 *
 * Image output of code using GD2
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
 
    define('QR_IMAGE', true);

    class QRimage {
    
        //----------------------------------------------------------------------
        public static function png($frame, $filename = false, $pixelPerPoint = 4, $outerFrame = 4,$saveandprint=FALSE) 
        {
            $image = self::image($frame, $pixelPerPoint, $outerFrame);
            
            if ($filename === false) {
                Header("Content-type: image/png");
                ImagePng($image);
            } else {
                if($saveandprint===TRUE){
                    ImagePng($image, $filename);
                    header("Content-type: image/png");
                    ImagePng($image);
                }else{
                    ImagePng($image, $filename);
                }
            }
            
            ImageDestroy($image);
        }
    
        //----------------------------------------------------------------------
        public static function jpg($frame, $filename = false, $pixelPerPoint = 8, $outerFrame = 4, $q = 85) 
        {
            $image = self::image($frame, $pixelPerPoint, $outerFrame);
            
            if ($filename === false) {
                Header("Content-type: image/jpeg");
                ImageJpeg($image, null, $q);
            } else {
                ImageJpeg($image, $filename, $q);            
            }
            
            ImageDestroy($image);
        }
    
        //----------------------------------------------------------------------
        private static function image($frame, $pixelPerPoint = 4, $outerFrame = 4) 
        {
            $h = count($frame);
            $w = strlen($frame[0]);
            
            $imgW = $w + 2*$outerFrame;
            $imgH = $h + 2*$outerFrame;
            
            $base_image =ImageCreate($imgW, $imgH);
            
            $col[0] = ImageColorAllocate($base_image,255,255,255);
            $col[1] = ImageColorAllocate($base_image,0,0,0);

            imagefill($base_image, 0, 0, $col[0]);

            for($y=0; $y<$h; $y++) {
                for($x=0; $x<$w; $x++) {
                    if ($frame[$y][$x] == '1') {
                        ImageSetPixel($base_image,$x+$outerFrame,$y+$outerFrame,$col[1]); 
                    }
                }
            }
            
            $target_image =ImageCreate($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);
            ImageCopyResized($target_image, $base_image, 0, 0, 0, 0, $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW, $imgH);
            ImageDestroy($base_image);
            
            return $target_image;
        }
    }
>>>>>>> 4573857f7021375d200db497d8d2485e86b528b0
