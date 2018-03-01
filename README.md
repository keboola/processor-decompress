# processor-decompress

[![Build Status](https://travis-ci.org/keboola/processor-decompress.svg?branch=master)](https://travis-ci.org/keboola/processor-decompress)

Takes all archive files in `/data/in/files` and decompresses them to `/data/out/files`. 

 - Currently supports ZIP and GZIP compressions.
 - Manifest files are ignored (and not copied).
   
# Usage

## GZIP

GZIP files are decompressed to a folder with the same name as the original archive.

### Example

#### Single file

Decompressing
```
/data/in/files/archive.csv.gz
```
results in 
```
/data/in/files/archive.csv.gz/archive.csv
```

#### Slices
Decompressing
```
/data/in/files/sliced-file/part1.csv.gz
/data/in/files/sliced-file/part2.csv.gz
/data/in/files/sliced-file/subfolder/part1.csv.gz
/data/in/files/sliced-file/subfolder/part2.csv.gz

```
results in 
```
/data/in/files/sliced-file/part1.csv.gz/part1.csv
/data/in/files/sliced-file/part2.csv.gz/part2.csv
/data/in/files/sliced-file/subfolder/part1.csv.gz/part1.csv
/data/in/files/sliced-file/subfolder/part2.csv.gz/part2.csv
```

## ZIP

ZIP files are extracted to the folder they're found in and the folder structure within the archive is preserved.

### Example
The `archive.zip` contains 2 files, `dummyfolder/slice1` and `dummyfolder/slice2`. Decompressing 
```
/data/in/files/archive.zip
/data/in/files/subfolder/archive.zip
```
results in
```
/data/out/files/archive.zip/dummyfolder/slice1
/data/out/files/archive.zip/dummyfolder/slice2
/data/out/files/subfolder/archive.zip/dummyfolder/slice1
/data/out/files/subfolder/archive.zip/dummyfolder/slice2

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
docker-compose run dev php /code/tests/run.php
```
 
# Integration
 - Build is started after push on [Travis CI](https://travis-ci.org/keboola/processor-decompress)
 - [Build steps](https://github.com/keboola/processor-decompress/blob/master/.travis.yml)
   - build image
   - execute tests against new image
   - publish image to ECR if release is tagged
