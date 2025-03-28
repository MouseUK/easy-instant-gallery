//This version will show all jpg and gif files in a director, allow download multiple in a zip file and clicking the image will load it full size in another tab. 
//This is fine for smaller images but will load slow/struggle to load if the images are larger which is why there are two versions.

<?php
$imageDir = '.'; // Use current directory for images
$images = array_merge(
    glob("$imageDir/*.jpg"),
    glob("$imageDir/*.JPG"),
    glob("$imageDir/*.gif"),
    glob("$imageDir/*.GIF")
);

// Pagination setup
$imagesPerPage = 25; 
$totalImages = count($images);
$totalPages = ceil($totalImages / $imagesPerPage);
$page = isset($_GET['page']) ? max(1, min($totalPages, intval($_GET['page']))) : 1;
$startIndex = ($page - 1) * $imagesPerPage;
$paginatedImages = array_slice($images, $startIndex, $imagesPerPage);

if (isset($_POST['download'])) {
    $selectedImages = $_POST['images'] ?? [];
    if (!empty($selectedImages)) {
        $zip = new ZipArchive();
        $zipName = 'selected_images.zip';
        if ($zip->open($zipName, ZipArchive::CREATE) === TRUE) {
            foreach ($selectedImages as $image) {
                $filePath = realpath("$image");
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
    <h2>Image Gallery</h2>
    <form method="post">
        <div class="gallery">
            <?php foreach ($paginatedImages as $image): ?>
                <?php $imageName = basename($image); ?>
                <div>
                    <a href="<?php echo $image; ?>" target="_blank">
                        <img src="<?php echo $image; ?>" alt="Image">
                    </a>
                    <label>
                        <input type="checkbox" name="images[]" value="<?php echo $imageName; ?>"> Select
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
