# processor-decompress

[![Build Status](https://travis-ci.org/keboola/processor-decompress.svg?branch=master)](https://travis-ci.org/keboola/processor-decompress)

Takes all archive files in `/data/in/files` and decompresses them to `/data/out/files`. 

 - Currently supports ZIP and GZIP compressions.
 - Manifest files are ignored (and not copied).
 - Slices are only supported for GZIP files.
   
# Usage

## GZIP

GZIP files are kept where they were originally stored (supporting subfolders), only the `.gz` suffix is removed.

### Example

#### Single file

Decompressing
```
/data/in/files/archive.csv.gz
```
results in 
```
/data/in/files/archive.csv
```

#### Slices
Decompressing
```
/data/in/files/sliced-file/part1.csv.gz
/data/in/files/sliced-file/part2.csv.gz
```
results in 
```
/data/in/files/sliced-file/part1.csv
/data/in/files/sliced-file/part2.csv
```

## ZIP

ZIP archive can contain multiple files, so the processor assumes it a sliced table. 
A folder with the same name (including `.zip` suffix) is created and the archive is decompressed to this folder.
Folder structure in the ZIP archive is ommited.

### Example
The `archive.zip` contains 2 files, `dummyfolder/slice1` and `dummyfolder/slice2`. Decompressing 
```
/data/in/files/archive.zip
```
results in
```
/data/out/files/archive.zip/file1
/data/out/files/archive.zip/file2
```

### Sample configuration

```
{
    "definition": {
        "component": "keboola.processor-decompress"
    }
}

```

## Development
 
Clone this repository and init the workspace with following command:

```
git clone https://github.com/keboola/processor-decompress
cd processor-decompress
docker-compose build
```

Run the test suite using this command:

```
docker-compose run tests
```
 
# Integration
 - Build is started after push on [Travis CI](https://travis-ci.org/keboola/processor-decompress)
 - [Build steps](https://github.com/keboola/processor-decompress/blob/master/.travis.yml)
   - build image
   - execute tests against new image
   - publish image to ECR if release is tagged
