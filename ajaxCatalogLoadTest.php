<?php

    $application = getenv("VCAP_APPLICATION");
    $application_json = json_decode($application, true);
    $applicationName = $application_json["name"];
    if (substr($applicationName, -3) === "-ui") { // if suffixed with "-ui", remove trailing "-ui"
        $catalogAppName = substr($applicationName, 0, -3)  . "-catalog";
    } else {
        $catalogAppName = $applicationName . "-catalog";
    }
    $applicationURI = $application_json["application_uris"][0];
    $catalogHost=substr_replace($applicationURI, $catalogAppName, 0, strlen($applicationName));
    $catalogRoute = "http://" . $catalogHost;

    if (isset($_GET['count'])) {
        $count = $_GET['count'];
    } else {
        $count = 100;
    }
    $url = $catalogRoute . "/loadTest?count=" . $count;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $curlResult = curl_exec($curl);
    $curlError = curl_error($curl);
    $curlErrno = curl_errno($curl);
    curl_close($curl);
    $firstChar = substr($curlResult, 0, 1); /* should check if $curlResult === FALSE if newer PHP */
    if ($firstChar != "{") {
        http_response_code(500);
        $errorObject = new stdClass();
        $errorObject->error = $curlError;
        $errorObject->errno = $curlErrno;
        $errorObject->url = $url;
        echo json_encode($errorObject);
        return;
    }
    echo $curlResult;

?>