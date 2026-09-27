<?php
/**
 * WBCE CMS
 * Responsive and Bootstrap based backend theme for WBCE
 *
 * @copyright 2016-2023 Jonathan Nessier, 2019-2023 Colinax, 2023 WBCE Team
 * @license GNU GPLv3
 */

// Include WBCE config file
include '../../../config.php';

if (function_exists('imagecreatefrompng')) {
    $fileName = 'backend-theme-favicon.png';
    $filePaths = [
        WB_PATH . DIRECTORY_SEPARATOR . $fileName,
        WB_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $fileName,
        WB_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'media-cms' . DIRECTORY_SEPARATOR . $fileName,
        WB_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'media-cms' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $fileName,
    ];

    if (isset($_GET['size']) && $_GET['size']) {
        foreach ($filePaths as $filePath) {
            if (is_file($filePath) && is_readable($filePath)) {

                // Set source image info
                $image = imagecreatefrompng($filePath);
                $imageWidth = imagesx($image);
                $imageHeight = imagesy($image);

                // Get requested favicon size
                $faviconSize = (int)$_GET['size'];

                // Check for a valid favicon size
                if ($faviconSize >= 16 && $faviconSize <= 256) {

                    // Check if source image size is not to large
                    if ($imageWidth <= 256 || $imageHeight <= 256) {

                        // Generate favicon base image
                        $faviconImage = imagecreatetruecolor($faviconSize, $faviconSize);
                        imagealphablending($faviconImage, false);
                        imagesavealpha($faviconImage, true);

                        // Set favicon transparency support
                        $transparent = imagecolorallocatealpha($faviconImage, 255, 255, 255, 127);
                        imagefilledrectangle($faviconImage, 0, 0, $faviconSize, $faviconSize, $transparent);

                        // Resize image tot the favicon size
                        imagecopyresampled($faviconImage, $image, 0, 0, 0, 0, $faviconSize, $faviconSize, $imageWidth, $imageHeight);

                        // Create and dump the image
                        header('Content-Type: image/png');
                        header("Cache-Control: private, max-age=10800, pre-check=10800");
                        header("Pragma: private");
                        header("Expires: " . date(DATE_RFC822, strtotime(" 1 day")));
                        imagepng($faviconImage, null, 9);
                    } else {
                        http_response_code(500);
                        echo 'Source image width and/or height is larger than 256 pixels';
                    }
                } else {
                    http_response_code(500);
                    echo 'Size is smaller than 16 pixels or larger than 256 pixels';
                }
                exit;
            }
        }
    }
}
http_response_code(404);
