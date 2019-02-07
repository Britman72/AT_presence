# AT Presence

AT_presence works with ActionTiles, DakBoard or other front end smarthome panel to display proper presence status tiles for presence captured via webCoRE and SmartThings. Currently the only presence shown in SmartThings is “Present” and “Not Present”. This project will give you the following benefits:

1. Display any location such as Home, Work, School, Mall, Gym etc. The number of locations is endless.
2. Shows a customized image instead of an icon.
3. Customize the tile size and background to match your existing tiles.

![Alt text](example/screenshot.JPG?raw=true "Title")

Presence is displayed as such:

At Home       - Presence is at home. Image will have a green circle.<br>
Away          - Presence is away from home and not at a predefined place/location. Image will have a red circle.<br>
At "location" - Presence is at a predefined place/location. Image will have a red circle.<br>
  
## Getting Started

These instructions will cover the steps needed to get your webCoRE presence into ActionTiles.

### Prerequisites

You will need to ensure you have already configured at least ONE presence device in webCoRE and SmartThings and have created at least ONE place/location in the webCoRE app on your phone, and that it is correctly updating presence for that device.

Furthermore, you will need the following:

1. ActionTiles, DakBoard or other front-end display.
2. SmartThings Classic App.
3. webCoRE.
4. Webserver with PHP and GD.
5. MySQL database.

## Web Server Installation Instructions

### Step 1.

Create the folder "AT_presence" on your webserver. Typically you will create the folder under your public_html folder.
Upload this project's contents to the folder, with the exception of the example folder.

### Step 2.

Create the MySQL database called “AT_presence”. Once created, assign/create a user with full permissions.

### Step 3

Open phpMyAdmin or your SQL tool of choice and connect to the AT_presence database.

Open the SQL editor and run:

```
CREATE TABLE `presence` (
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `place` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` datetime NOT NULL,
  `last place` varchar(50) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `presence`
  ADD UNIQUE KEY `idx_presence_id_name` (`name`) USING BTREE;
COMMIT;

```

This table is required to store the latest presence status via WebCORE.

### Step 4

In your web server file manager, edit both files:

```
getpresence.php
updatepresence.php
```

Change the login information at the top of each script to match your database and user id.

```
$servername = "localhost";
$username = "<your username>";
$password = "<your password>";
$dbname = "<your AT_presence database>";
```

### Step 5

Upload the profile images for each person you’re capturing presence for. For best results use square cropped JPG images no more than 300x300 pixels. 

Give each image the same name as the person eg. dad.jpg, mom.jpg, tim.jpg etc.

Upload each file to the AT_presence\userprofiles folder on your web server.

## webCoRE Piston Instructions

Before completing the below you will need to ensure you have already setup webCoRE and connected it to SmartThings so that your presence devices are already setup and working. You will also need to ensure you have configured places/locations within the WebCORE app on your phone.

### Step 1 

Open your webCoRE dashboard in a web browser and create a new piston.

Click “Restore a piston from a backup code” and give it a name like “Update Presence - <name of person>” example: Update Presence - Dad.

Enter **h6fo** for the backup bin code and click Create.

This will create a new piston from the template.

### Step 2

Now it will walk you through rebuilding the piston items and you should get a dialog box asking you to enter a URL. Enter:

```
http://www.yourserver.com/AT_presence/updatepresence.php
```

### Step 3

Change the string “name” to the name of the person you are updating. Only enter one name here.

```
   define 
        string name = 'Dad';
```

### Step 4

Next you will need to update the presence device and place/location.

Click on each line where you see {:xxxxxxxxxxxxxxxxxxxxxxxxxxxx0:} and select your presence device from the list. For example, choose 'Dad'.

Change Home, Work, Gym etc. to all the places/locations that you’ve already defined in webCoRE. Comma separate each one.

Repeat this process throughout the piston code.

Note that by default the piston will also send a push notification when the person’s presence changes. You can remove this if need be but it's good for initial testing.

Another note, the piston includes a rapidfire condition which prevents the presence from triggering multuple times within a 5 minute period. If you are experiencing the presence triggering too often try playing with the value until you no longer get multiple updates. I found 5 minutes to be the sweet spot.

### Step 5

Repeat the process of creating a new piston for each and every person. The end result you will have one piston per person.

### Step 6

Run the pistons and ensure that the presence is being written to the presence table in your AT_presence MySQL database. You should see one line item per person along with the current status and date/time. If you do not see this then you will need to test to make sure that the updatepresence.php script is able to write to the table.

Note: There needs to be a record/status in the presence table before continuing below.


## ActionTiles Instructions

### Step 1

Open your ActionTiles account and click My Media.

Add a new media tile and choose “This Media is a still image or GIF”

For the URL paste in:

```
http://www.yourwebserver.com/AT_presence/<tile size>/<tile color>/<text color>&name=[name]&detail=[1|0]&bgalpha=[0|127]

tile size = 	the size of your tile eg. 200x200
tile color = 	the background HEX color of your tile (# not required) eg. 333333
text color =	the text HEX color (# not required) etg. Ffffff
name = 	      the name of the person eg. Dad.
detail =      1=Show "Arrived/Left HH:MMAMPM" below the place. 0=Do not show this line.
bgalpha =     0-127 with 0 being opaque and 127 being 100% transparent.
```

Example:

```
http://www.yourwebserver.com/AT_presence/200x200/333/fff&name=Dad&detail=1&bgalpha=0
```
This will display a tile 200x200 pixels in size with a background color of #333 and text color of #fff, with a detail like below the place (ie Arrived/Left Home) with a solid background transparency.

Set the refresh rate to something meaningful such as 60 seconds or more.

If the URL works your image will be displayed like so:

![Alt text](example/screenshot2.JPG?raw=true "Title")

### Step 2

Repeat adding more media tiles for each person.

## DakBoard Instructions

You will need a premium or higher license for DakBoard in order to be able to use a custom layout. With your custom layout, add an image URL and use the same URL as above for ActionTiles. Use the bgalpha parameter to control the transparency.

## Authors

* **Al Lougher** - *Initial work* 

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* Ruquay K Calloway
* ActionTiles  
