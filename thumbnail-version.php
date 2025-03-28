// Simple quick Gallery in a folder, no db needed, just php on the server.
// Drop this into a folder with images, when you go to the page it will load slow first time, it creats a filename-thumb.jpg for every image in the folder that its trying to load.
// (If there is more than 25 images it will only do the first 25, when you go to the next page it will do the next 25 etc)
// It will use the Thumb's to display the image after this first step, making it quicker to load.
// Download function will take the origional image, clicking the thumb image will take you to the real image.
// This was a quick and easy way of creating a gallery of photos I took at an event for a club and allow them to get a copy of the pictures they wanted easily.

// known bug
// ***This will show the image twice if the .jpg is .JPG which I'm yet to fix***

<?php
$imageDir = '.';

// Gather all the original images (not the thumbs)
$allImages = array_merge(
    glob("$imageDir/*.jpg"),
    glob("$imageDir/*.JPG"),
    glob("$imageDir/*.gif"),
    glob("$imageDir/*.GIF")
);

// Filter out any files that are already thumbnails
$originalImages = array_filter($allImages, function ($image) {
    return !preg_match('/-thumb\.(jpg|gif)$/i', $image);
});

// Re-index array to prevent gaps
$originalImages = array_values($originalImages);
// Create thumbnails only for images that don't already have a thumbnail
function createThumbnail($imagePath) {
    $info = pathinfo($imagePath);
    
    // Check if it's already a thumbnail
    if (strpos($info['filename'], '-thumb') !== false) {
        return $imagePath;
    }
    
    // Determine correct thumbnail extension
    $thumbExt = strtolower($info['extension']) === 'gif' ? 'gif' : 'jpg';
    $thumbPath = $info['dirname'] . '/' . $info['filename'] . '-thumb.' . $thumbExt;
    
    if (file_exists($thumbPath)) {
        return $thumbPath;
    }
    
    list($width, $height) = getimagesize($imagePath);
    $newWidth = 150;
    $newHeight = ($height / $width) * $newWidth;
    
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    
    switch (strtolower($info['extension'])) {
        case 'jpg':
        case 'jpeg':
            $source = imagecreatefromjpeg($imagePath);
            break;
        case 'gif':
            $source = imagecreatefromgif($imagePath);
            break;
        default:
            return $imagePath; // Return original if unsupported format
    }
    
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    switch ($thumbExt) {
        case 'jpg':
            imagejpeg($thumb, $thumbPath, 80);
            break;
        case 'gif':
            imagegif($thumb, $thumbPath);
            break;
    }
    
    imagedestroy($thumb);
    imagedestroy($source);
    
    return $thumbPath;
}

// Pagination setup
$imagesPerPage = 50;
$totalImages = count($originalImages);
$totalPages = ceil($totalImages / $imagesPerPage);
$page = isset($_GET['page']) ? max(1, min($totalPages, intval($_GET['page']))) : 1;
$startIndex = ($page - 1) * $imagesPerPage;
$paginatedImages = array_slice($originalImages, $startIndex, $imagesPerPage);

// Handle download of selected images
if (isset($_POST['download'])) {
    $selectedImages = $_POST['images'] ?? [];
    if (!empty($selectedImages)) {
        $zip = new ZipArchive();
        $zipName = 'selected_images.zip';
        if ($zip->open($zipName, ZipArchive::CREATE) === TRUE) {
            foreach ($selectedImages as $image) {
                $filePath = realpath("$imageDir/$image");
                if ($filePath) {
                    $zip->addFile($filePath, $image);
                }
            }
            $zip->close();
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename=' . $zipName);
            header('Content-Length: ' . filesize($zipName));
            readfile($zipName);
            unlink($zipName);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Gallery</title>
    <style>
        .gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .gallery div {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        img {
            width: 150px;
            height: auto;
            cursor: pointer;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid #000;
        }
    </style>
</head>
<body>
    <h2><center>Skating Edge Artistic - Image Gallery</center></h2>
    <h3><center>Great Yarmouth Nationals 22/03/2025 - 23/03/2025</center></h3><br>
    <form method="post">
        <div class="gallery">
            <?php foreach ($paginatedImages as $image): ?>
                <?php 
                    $imageName = basename($image); 
                    $thumbPath = createThumbnail($image); 

                    // Remove "-thumb" from the thumbnail path to get the original file path
                    $originalImagePath = preg_replace('/-thumb\.(jpg|gif)$/i', '.$1', $thumbPath);
                ?>
                <div>
                    <!-- Show the thumbnail and link it to the original image -->
                    <a href="<?php echo htmlspecialchars($originalImagePath); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($thumbPath); ?>" alt="Image">
                    </a>
                    <!-- The checkbox value should be the original image file, not the thumb -->
                    <label>
                        <input type="checkbox" name="images[]" value="<?php echo htmlspecialchars($imageName); ?>"> Select
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="submit" name="download">Download Selected</button>
    </form>
    
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">Previous</a>
        <?php endif; ?>
        Page <?php echo $page; ?> of <?php echo $totalPages; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Next</a>
        <?php endif; ?>
    </div>
</body>
</html>
