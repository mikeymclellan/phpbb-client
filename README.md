# PHPBB Client

A quick-n-dirty client to search & replace across all your posts made to a PHPBB forum without administrator access.

The main use-case is to allow me to move all my forum images from Photobucket to another image service. 

This doesn't require an API plugin installed on the forum, it will simply login and use the frontend like a normal user 
would with a web browser.

## Installation

Make sure you have PHP7 installed, [install composer](https://getcomposer.org/) then run:

```
git clone phpbb-client
cd phpbb-client
composer install
```

## Usage 

The only client command implemented is a search and replace accross all of your posts. 
You must provide a username & password and search & replace strings. 

The search string is a [Regex](https://www.jotform.com/blog/php-regular-expressions/) and the replace string is designed 
to use regex replacements. 

e.g

```
./console.php replace --username mikey --password xxxxxx --base-url 'https://oldschool.co.nz/index.php?' 'http://i1230.photobucket.com/albums/ee490/mmcl055/(.*)' 'https://s3-ap-southeast-2.amazonaws.com/forum-media/photobucket/\1'
```

## Migrating Images From Photobucket

The main use for this is to allow me to move all my forum post images from Photobucket to [S3](https://aws.amazon.com/s3/), but this applies to migrating to other providers.

First you'll need to export all your Photobucket images and upload them somewhere else. Some instructions on exporting your Photobucket images can be found in this [Reddit post](https://www.reddit.com/r/photobucket/comments/8ifn3s/how_to_download_all_of_your_photobucket_photos_at/).

Then upload those images somewhere else, I chose S3. You can then run this command as above, substituting the search and replace strings with something sensible for your accounts.