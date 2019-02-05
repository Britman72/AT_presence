<?php
/*
Copyright (c) 2018 Al Lougher

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/
/*
if ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) {
	// If the browser has a cached version of this image, send 304
	header( 'Last-Modified: ' . $_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304 );
	exit;
}
*/

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Get the name

$params = array_change_key_case($_GET);
$name = $params['name'];

if(isset($name)) {
    $name = $name;
} else {
    exit("You need to provide at least one name!");
}

// Get the values from the presence table
$servername = "localhost";
$username = "<your username>";
$password = "<your password>";
$dbname = "<your AT_presence database>";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "SELECT * FROM presence where name='" . $name . "'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $sname = $row["name"];
        $sstatus = $row["status"];
    }
} else {
    $conn->close();
    exit("Sorry you have no status stored!");
}

$conn->close();

if(isset($sname)) {
    // do nothing
} else {
    exit("Could not find name in the database!");
}

//Ruquay K Calloway http://ruquay.com/sandbox/imagettf/ made a better function to find the coordinates of the text bounding box so I used it.
function imagettfbbox_t( $size, $text_angle, $fontfile, $text ){
	// Compute size with a zero angle
	$coords = imagettfbbox( $size, 0, $fontfile, $text );

	// Convert angle to radians
	$a = deg2rad( $text_angle );

	// Compute some usefull values
	$ca = cos( $a );
	$sa = sin( $a );
	$ret = array();

	// Perform transformations
	for ( $i = 0; $i < 7; $i += 2 ) {
		$ret[ $i ] = round( $coords[ $i ] * $ca + $coords[ $i+1 ] * $sa );
		$ret[ $i+1 ] = round( $coords[ $i+1 ] * $ca - $coords[ $i ] * $sa );
	}
	return $ret;
}

// Get the query string from the URL. x would = 600x400 if the url was http://dummyimage.com/600x400
$x = strtolower( $_GET['x'] );
// If the first character of $x is a / then get rid of it
if ( $x[0] == '/' ) {
	$x = ltrim( $x, '/' );
}
$x_pieces = explode( '/', $x );

// To easily manipulate colors between different formats
include("color.class.php");

// Find the background color which is always after the 2nd slash in the url
$bg_color = 'ccc';
if ( isset( $x_pieces[1] ) ) {
	$bg_color_parts = explode( '.', $x_pieces[1] );
	if ( isset( $bg_color_parts[0] ) && ! empty( $bg_color_parts[0] ) ) {
		$bg_color = $bg_color_parts[0];
	}
}
$background = new color();
$background->set_hex( $bg_color );

// Find the foreground color which is always after the 3rd slash in the url
$fg_color = '000';
if ( isset( $x_pieces[2] ) ) {
	$fg_color_parts = explode( '.', $x_pieces[2] );
	if ( isset( $fg_color_parts[0] ) && ! empty( $fg_color_parts[0] ) ) {
		$fg_color = $fg_color_parts[0];
	}
}
$foreground = new color();
$foreground->set_hex($fg_color);

// Determine the file format. This can be anywhere in the URL.
$file_format = 'png';
preg_match_all( '/(gif|jpg|jpeg)/', $x, $result );
if ( isset( $result[0] ) && isset( $result[0][0] ) && $result[0][0] ) {
	$file_format = $result[0][0];
}

// Find the image dimensions
if ( substr_count( $x_pieces[0], ':' ) > 1 ) {
	die('Too many colons in the dimension paramter! There should be 1 at most.');
}

if ( strstr( $x_pieces[0], ':' ) && ! strstr( $x_pieces[0], 'x' ) ) {
	die('To calculate a ratio you need to provide a height!');
}
// Dimensions are always the first paramter in the URL
$dimensions = explode( 'x', $x_pieces[0] );

// Filter out any characters that are not numbers, colons or decimal points
$width = preg_replace( '/[^\d:\.]/i', '', $dimensions[0] );
$height = $width;
if ( $dimensions[1] ) {
	$height = preg_replace( '/[^\d:\.]/i', '', $dimensions[1] );
}

// If the dimensions are too small then kill the script
if ( $width < 1 || $height < 1 ) {
	die("Too small of an image!");
}

// If one of the dimensions has a colon in it, we can calculate the aspect ratio. Chances are the height will contain a ratio, so we'll check that first.
if ( preg_match ( '/:/', $height ) ) {
	$ratio = explode( ':', $height );

	// If we only have one ratio value, set the other value to the same value of the first making it a ratio of 1
	if ( ! $ratio[1] ) {
		$ratio[1] = $ratio[0];
	}

	if ( ! $ratio[0] ) {
		$ratio[0] = $ratio[1];
	}

	$height = ( $width * $ratio[1] ) / $ratio[0];
} else if( preg_match ( '/:/' , $width) ) {
	$ratio = explode( ':', $width );
	//If we only have one ratio value, set the other value to the same value of the first making it a ratio of 1
	if ( ! $ratio[1] ) {
		$ratio[1] = $ratio[0];
	}

	if ( !$ratio[0] ) {
		$ratio[0] = $ratio[1];
	}

	$width = ($height * $ratio[0]) / $ratio[1];
}

//Limit the size of the image to no more than an area of 16,000,000
$area = $width * $height;
if ( $area >= 16000000 || $width > 9999 || $height > 9999 ) {
	die("Too big of an image!");
}

//Let's round the dimensions to 3 decimal places for aesthetics
$width = round( $width, 3 );
$height = round( $height, 3 );

//I don't use this but if you wanted to angle your text you would change it here.
$text_angle = 0;

 // If you want to use a different font simply upload the true type font (.ttf) file to the same directory as this PHP file and set the $font variable to the font file name. I'm using the M+ font which is free for distribution -> http://www.fontsquirrel.com/fonts/M-1c
$font = dirname(__FILE__) . '/fonts/arial.ttf';

// Create an image
$img = imageCreateTrueColor( $width, $height );

$bg_color = imagecolorallocate( $img, $background->get_rgb('r'), $background->get_rgb('g'), $background->get_rgb('b') );
$fg_color = imagecolorallocate( $img, $foreground->get_rgb('r'), $foreground->get_rgb('g'), $foreground->get_rgb('b') );

$icon = "userprofiles/" . strtolower($name) . ".jpg";

//$src = imagecreatefrompng($icon);
$image_s = imagecreatefromstring(file_get_contents($icon));

// Now lets resize the image to 256x256
$iwidth = imagesx($image_s);
$iheight = imagesy($image_s);
$newwidth = 256;
$newheight = 256;

$image = imagecreatetruecolor($newwidth, $newheight);
imagefill($image, 0, 0, $bg_color );
imagecopyresampled($image, $image_s, 0, 0, 0, 0, $newwidth, $newheight, $iwidth, $iheight);

//create masking
$mask = imagecreatetruecolor($newwidth, $newheight);
$transparent = imagecolorallocate($mask, $background->get_rgb('r'), $background->get_rgb('g'), $background->get_rgb('b'));
imagecolortransparent($mask, $transparent);

// Add the border first. Red=Away. Green=Home.
if($sstatus == "At Home") {
    $color = imagecolorallocate($img, 87, 199, 38);
} else {
    $color = imagecolorallocate($img, 255, 0, 0);
}
imagefilledellipse($mask, $newwidth/2, $newheight/2, $newwidth-2, $newheight-2, $color);

// Add the actual image
imagefilledellipse($mask, $newwidth/2, $newheight/2, $newwidth-16, $newheight-16, $transparent);
$red = imagecolorallocate($mask, $background->get_rgb('r'), $background->get_rgb('g'), $background->get_rgb('b'));
imagecopymerge($image, $mask, 0, 0, 0, 0, $newwidth, $newheight, 100);
imagecolortransparent($image, $red);
imagefill($image, 0, 0, $red);

$src=$image;

// Now lets combine the profile image with the tile and overlay the text
// Determine where to set the X position of the overlay icon so it is centered
$imgX = ceil( ( $width - ($width*.45 ) ) / 2 );
$imgY = ceil( ( $height - ($height*.45) ) / 2 );

imagealphablending( $img, true );
imagefill($img, 0, 0, $bg_color);
imagecopyresampled($img, $src, $imgX, $imgY, 0, 0, $width*.45, $height*.45, 256, 256);
imagesavealpha( $img, true );

// AL
// Make the text from the database values
// We should have at least two values. The presecence name and the presence place.
// Name goes at the top. Place at the bottom.
// Overlay the location icon on the image.

$lines=2;

/*
if ( empty( $_GET['text'] ) || ! isset( $_GET['text'] ) ) {
	preg_match( '/&text=(.+)/i', $_GET['x'], $matches );
	if ( isset( $matches[1] ) ) {
		$_GET['text'] = urldecode( $matches[1] );
	}
}

if ( isset( $_GET['text'] ) && $_GET['text'] ) {
	$_GET['text'] = preg_replace_callback(
		"/(0x[0-9A-F]{,3})/ui",
		function( $matches ) {
			return chr( hexdec( $matches[0] ) );
		},
		$_GET['text']
	);
	$lines = substr_count( $_GET['text'], '|' );
	$text = preg_replace( '/\|/i', "\n", $_GET['text'] );
} else {
	$lines = 1;
	// This is the default text string that will go right in the middle of the rectangle
	// &#215; is the multiplication sign, it is not an 'x'
	$text = $width." &#215; ".$height;
}
*/

// Ric Ewing: I modified this to behave better with long or narrow images and condensed the resize code to a single line
$fontsize1 = round(($width*.9) / 10);
$fontsize2 = round(($width*.9) / 10);

//$fontsize1 = max( min( $width / (strlen($sname) *. , $height * 0.5 ), 5 );
//$fontsize2 = max( min( $width / strlen($splace) , $height * 0.5 ), 5 );

// Pass these variable to a function to calculate the position of the bounding box
$textBox1 = imagettfbbox_t($fontsize1, $text_angle, $font, $sname);
$textBox2 = imagettfbbox_t($fontsize2, $text_angle, $font, $sstatus);

// Calculate the width of the text box by subtracting the upper right "X" position with the lower left "X" position
$textWidth1 = ceil( ( $textBox1[4] - $textBox1[1] ) * 1.07 );
$textWidth2 = ceil( ( $textBox2[4] - $textBox2[1] ) * 1.07 );

// Calculate the height of the text box by adding the absolute value of the upper left "Y" position with the lower left "Y" position
$textHeight1 = ceil( ( abs( $textBox1[7] ) + abs( $textBox1[1] ) ) * 1 );
$textHeight2 = ceil( ( abs( $textBox2[7] ) + abs( $textBox2[1] ) ) * 1 );

//Determine where to set the X position of the text box so it is centered
$textX1 = ceil( ( $width - $textWidth1 ) / 2 );
$textX2 = ceil( ( $width - $textWidth2 ) / 2 );

//Determine where to set the Y position of the text box so it is centered
//$textY = ceil( ( $height - $textHeight ) / 2 + $textHeight );

//Create the rectangle with the specified background color
//imageFilledRectangle( $img, 0, 0, $width, $height, $bg_color );

//Create and positions the text
imagettftext( $img, $fontsize1, $text_angle, $textX1, $fontsize1 + ($height/$fontsize1), $fg_color, $font, $sname );
imagettftext( $img, $fontsize2, $text_angle, $textX2, $height-$fontsize2, $fg_color, $font, $sstatus );
//imagettftext( $img, $fontsize1, $text_angle, $textX1, 0, $fg_color, $font, $sname );
//imagettftext( $img, $fontsize2, $text_angle, $textX2, 70, $fg_color, $font, $splace );


function drawborder($source,$r,$x,$y,$color){
  for($i = 0;$i<=2*pi();$i+=(pi()/180)){
    imageline($source,cos($i)*$r+$x,sin($i)*$r+$y,
      cos($i+(pi()/180))*$r+$x,sin($i+(pi()/180))*$r+$y,$color);
  }
}

function process_output_buffer( $buffer = '' ) {
	$buffer = trim( $buffer );
	if( strlen( $buffer ) == 0 ) {
		return '';
	}
	return $buffer;
}
// Start output buffering so we can determine the Content-Length of the file
ob_start( 'process_output_buffer' );

// Create the final image based on the provided file format.
switch ( $file_format ) {
	case 'gif':
		imagegif( $img );
	break;
	case 'png':
		imagepng( $img );
	break;
	case 'jpg':
	case 'jpeg':
		imagejpeg( $img );
	break;
}
$output = ob_get_contents();

ob_end_clean();

// Caching Headers
$offset = 60 * 60 * 24 * 90; //90 Days
header( 'Cache-Control: public, max-age=' . $offset );
// Set a far future expire date. This keeps the image locally cached by the user for less hits to the server
header( 'Expires: ' . gmdate( DATE_RFC1123, time() + $offset ) );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', time() ) . ' GMT' );
// Set the header so the browser can interpret it as an image and not a bunch of weird text
header( 'Content-type: image/png' );
header( 'Content-Length: ' . strlen( $output ) );

echo $output;
