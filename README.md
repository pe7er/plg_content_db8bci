# plg_content_db8bci - Content - db8 Backend Content Images

Joomla 4/5 content plugin

## Available Languages
- English (en-GB)

## Installation
Install like any other Joomla 4 or Joomla 5 plugin.

## Configuration
After installation, configure the plugin via: 
- System > Plugins > select ``Content - db8 Backend Content Images``
- Under ``Backend Article Images`` configure if you want to display Intro Images from Articles (or placeholder) and the maximum width and height. 
- Under ``Backend Category Images`` configure if you want to display Category Images (or placeholder) and the maximum width and height.
- Save the plugin

## Result
In the backend lists of Categories and Articles will display an image (or placeholder) if they contain any.

## Changelog

### 1.0.4 - April 2028
- Fixed issue with missing thumbnails when using filters in links like /administrator/index.php?option=com_content&filter[category_id]=11&filter[published]=1&filter[level]=1
- Fixed issue with missing thumbnails Categories in links like /administrator/index.php?option=com_categories&extension=com_content
