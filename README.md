# PHP package for WebEx NBR API (XML API inprogress)
----------------------------------------------------

### Installation

To install this package you will need:

 - PHP >= 5.3

Run this command to install via composer

```
composer require sathish/webex:dev-master
```

or edit the `composer.json` 

```
"require": {
    "sathish/webex": "dev-master"
}
```
| Available NBR API Methods | Available (in this package) |
| --------------------------|:---------------------------:|
| deleteMeetingXML          |  No                         |
| deleteNBRStorageFile      |  No                         |
| downloadFile              |  No                         | 
| downloadNBRStorageFile    |  Yes                        | 
| downloadWAVFile           |  No                         | 
| getMeetingTicket          |  No                         | 
| getNBRConfIdList          |  No                         | 
| getNBRRecordIdList        |  Yes                        | 
| getNBRStorageFile         |  No                         | 
| getSCXML                  |  No                         | 
| getStorageAccessTicket    |  Yes                        | 

To know more about WebEx NBR API https://developer.cisco.com/site/webex-developer/develop-test/nbr-web-services-api/api-functions.gsp

## Usage

```
    $nbr = new WebExNBR('<admin username>', '<admin password>', '<site id>');
    $nbr->setServiceUrl('<NBR API URL>'); /** Don't append slash(/) at the end **/
    $nbr->generateTicket();
```    
### To download recording (Response will be in multipart format)

```
    $nbr->downloadRecording('<recoding id>');
```

### Retrieve recording list
 
```
   $nbr->recordingList();
```

### Regenerate ticket after 1 hour
 
```
   $nbr->refreshTicket();
```

Use Riverline\MultiPartParse package to parse the downloadRecording response. For more info https://github.com/Riverline/multipart-parser

Note: PRs are welcomed.
