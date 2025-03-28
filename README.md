# easy-instant-gallery
Drop file in as index.php (or whatever you want.php) into a folder on a server with PHP setup, all images in that folder and now viewable in a gallery type page.

2 Versions, 
Simple version just shows the images, 25 at a time with a page function to allow you to see more than 25 images.
It loads them no matter the size so will be slow to load for large images.

Thumb version is for larger images and more for a more perment gallery use than just a quick "what are these images in this folder" use.
It will create a thumbnail for each .jpg and .gif in the folder for the first 50 images, this will be slow. Once that has been done once it will use the thumb instead and will load up faster.
This will only do it on the first time the image(s) are viewed, once it has a thumb for the image name it wont do this again.

Known issue.
If you have .JPG (upper case) in the folder, it will show the image twice, if the files are .jpg (lower case) it only shows it once. *Not needed to fix this yet.

Quick and dirty script created when I needed to display and allow download of 380 10-15Mb images.
Adding it on here incase I need it again or its of use to anyone else.

Enjoy.
