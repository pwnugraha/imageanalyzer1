 <?php
require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Image Analyzer - Azure Computer Vision</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
</head>
<body>
  <h1>Analisa Gambar</h1>
 <p>Pilih gambar dan klik <strong>Upload</strong> untuk menganalisa gambar.</p>
 <form method="post" action="index.php" enctype="multipart/form-data" >
       <input type="file" name="fileToUpload" id="fileToUpload"/></br></br>
       <input type="submit" name="submit" value="Upload" />
 </form>

<?php
$connectionString = "DefaultEndpointsProtocol=https;AccountName=storage1webapp;AccountKey=wb9TokBkPMTowSBHwgZB2OjAIpWPQtUkmm/7ueHfaDRH8bstDgDP97wFPLsiWRg11eavMnFqV0FI9fVWNnC20w==";

// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);

if (isset($_POST["submit"])) {
	
	$file_type = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
	if(!in_array($_FILES["fileToUpload"]["type"], $file_type)){
		echo '<br/>Pastikan tipe file yang diupload adalah gambar.';
		die();
	}
    // Create container options object.
    $createContainerOptions = new CreateContainerOptions();

    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);

    // Set container metadata.
    $createContainerOptions->addMetaData("key1", "value1");
    $createContainerOptions->addMetaData("key2", "value2");

      $containerName = "pwncontainer".generateRandomString();

    try {
		$fileToUpload = $_FILES["fileToUpload"]["name"];
        // Create container.
        $blobClient->createContainer($containerName, $createContainerOptions);

        //Upload blob
        $blobClient->createBlockBlob($containerName, $fileToUpload, fopen($_FILES["fileToUpload"]["tmp_name"], 'r'));
		
		?>
<br/>
<div id="wrapper" style="width:1020px; display:table;">

    <div id="imageDiv" style="width:420px; display:table-cell;">
        Gambar berhasil diupload:
        <br>
		<figure>
        <img id="sourceImage" src="https://storage1webapp.blob.core.windows.net/<?= $containerName ?>/<?= $fileToUpload?>" width="400" />
		<br/>
		<figcaption id="responseTextArea"></figcaption>
		</figure>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var subscriptionKey = "8c9ad34e53a34806b037fbfb5789609a";
 
        var uriBase =
            "https://pwnvision.cognitiveservices.azure.com/vision/v2.0/analyze";
 
        // Request parameters.
        var params = {
            "visualFeatures": "Description",
            "details": "",
            "language": "en",
        };
 
        // Display the image.
        var sourceImageUrl = document.querySelector("#sourceImage").src;
 
        // Make the REST API call.
        $.ajax({
            url: uriBase + "?" + $.param(params),
 
            // Request headers.
            beforeSend: function(xhrObj){
                xhrObj.setRequestHeader("Content-Type","application/json");
                xhrObj.setRequestHeader(
                    "Ocp-Apim-Subscription-Key", subscriptionKey);
            },
 
            type: "POST",
 
            // Request body.
            data: '{"url": ' + '"' + sourceImageUrl + '"}',
        })
 
        .done(function(data) {
            // Show formatted JSON on webpage.
            $("#responseTextArea").html(JSON.stringify(data.description.captions[0].text));
        })
 
        .fail(function(jqXHR, textStatus, errorThrown) {
            // Display error message.
            var errorString = (errorThrown === "") ? "Error. " :
                errorThrown + " (" + jqXHR.status + "): ";
            errorString += (jqXHR.responseText === "") ? "" :
                jQuery.parseJSON(jqXHR.responseText).message;
            alert(errorString);
        });
    });
</script>
<?php
    }
    catch(ServiceException $e){
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
    catch(InvalidArgumentTypeException $e){
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
} 
?>

</body>
</html>