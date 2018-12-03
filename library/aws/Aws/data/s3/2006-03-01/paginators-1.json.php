<?php
/**
 * Paginators config
 *
 * @package WP2Static
 */

// This file was auto-generated from sdk-root/src/data/s3/2006-03-01/paginators-1.json
return [ 'pagination' => [ 'ListBuckets' => [ 'result_key' => 'Buckets', ], 'ListMultipartUploads' => [ 'input_token' => [ 'KeyMarker', 'UploadIdMarker', ], 'limit_key' => 'MaxUploads', 'more_results' => 'IsTruncated', 'output_token' => [ 'NextKeyMarker', 'NextUploadIdMarker', ], 'result_key' => [ 'Uploads', 'CommonPrefixes', ], ], 'ListObjectVersions' => [ 'input_token' => [ 'KeyMarker', 'VersionIdMarker', ], 'limit_key' => 'MaxKeys', 'more_results' => 'IsTruncated', 'output_token' => [ 'NextKeyMarker', 'NextVersionIdMarker', ], 'result_key' => [ 'Versions', 'DeleteMarkers', 'CommonPrefixes', ], ], 'ListObjects' => [ 'input_token' => 'Marker', 'limit_key' => 'MaxKeys', 'more_results' => 'IsTruncated', 'output_token' => 'NextMarker || Contents[-1].Key', 'result_key' => [ 'Contents', 'CommonPrefixes', ], ], 'ListObjectsV2' => [ 'input_token' => 'ContinuationToken', 'limit_key' => 'MaxKeys', 'output_token' => 'NextContinuationToken', 'result_key' => [ 'Contents', 'CommonPrefixes', ], ], 'ListParts' => [ 'input_token' => 'PartNumberMarker', 'limit_key' => 'MaxParts', 'more_results' => 'IsTruncated', 'output_token' => 'NextPartNumberMarker', 'result_key' => 'Parts', ], ],];
