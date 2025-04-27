# Vault

A simple file storage server built with Laravel

## Get the root folder contents
```
curl "https://vault.test/api/folders" \
-H 'Authorization: Bearer ...'
```

## Get the folder contents
```
curl "https://vault.test/api/folders/9ec08e65-77f9-401b-a5b3-862054671130" \
-H 'Authorization: Bearer ...'
```

## Upload a file
```
curl -X "POST" "https://vault.test/api/folders/9ec08e65-77f9-401b-a5b3-862054671130/files" \
-H 'Authorization: Bearer ...' \
-H 'Content-Type: multipart/form-data; charset=utf-8; boundary=__X_PAW_BOUNDARY__' \
-F "file="
```
