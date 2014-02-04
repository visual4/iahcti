<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 28.01.14
 * Time: 09:48
 */

interface UcpV22FaxRequests {

    /**
     * The UCP client sends this request in order to upload a file to the server. With the request the client provides additional
     * information about the file being transferred. Upon reception the server can decide to accept (it returns an ID) or reject the
     * transfer (it returns an empty string).
     *
     * @param array $fileInfo description, fileName, mimeType, fileSize for the file upload
     * @param string $digestName The name of the hash algorithm used during file transfer (MD5 or SHA)
     * @return array fileTransferInfo fileTransferId (the UUID for this transfer) and fileTransferState (one of the FileTransferState values)
     */
    public function rpcFileUpload_InitializeUpload($fileInfo, $digestName='SHA');

} 