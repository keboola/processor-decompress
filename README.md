# processor-decompress

[![Build Status](https://travis-ci.org/keboola/processor-decompress.svg?branch=master)](https://travis-ci.org/keboola/processor-decompress)

Takes all archive files in `/data/in/files` and decompresses them to `/data/out/files`.

 - Currently supports ZIP and GZIP compressions.
 - Manifest files are ignored (and not copied).

# Usage

Compression type is detected from file suffix (`.zip`, `.gz` or `.snappy`) or can be forced by using optional `compression_type` parameter.

## Parameters

Processor supports these optional parameters:

 - `compression_type` -- Specify compression type `zip`, `gzip` or `snappy`, files can have any name or suffix and are decompressed using the specified method.

## Sample configurations

### Detect compression type automatically

```
{
    "definition": {
        "component": "keboola.processor-decompress"
    }
}

```

### Specify compression type

```
{
    "definition": {
        "component": "keboola.processor-decompress"
    },
    "parameters": {
        "compression_type": "zip"
    }
}

```

### Graceful decompression

With default setting (`"graceful": false`), when the processor encounters a file that cannot be decompressed, it will fail. In 
graceful mode, the failing file will be skipped and reported in events. Graceful mode is set with the `"graceful": true` parameter.
  
```
{
    "definition": {
        "component": "keboola.processor-decompress"
    },
    "parameters": {
        "graceful": true
    }
}

```

# Decompression details

## GZIP

GZIP files are decompressed to a folder with the same name as the original archive.
The decompressed file will be created without the `.gz` suffix, if present.

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

ZIP files are extracted to a subfolder carrying the archive name and the folder structure within the archive is preserved.

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

## Snappy

Snappy files are decompressed to a folder with the same name as the original archive.
The decompressed file will be created without the `.snappy` suffix, if present.

Snappy decompressor uses ![python-snappy](https://github.com/andrix/python-snappy) library.

### Example

#### Single file

Decompressing
```
/data/in/files/archive.csv.snappy
```
results in
```
/data/in/files/archive.csv.snappy/archive.csv
```

#### Slices
Decompressing
```
/data/in/files/sliced-file/part1.csv.snappy
/data/in/files/sliced-file/part2.csv.snappy
/data/in/files/sliced-file/subfolder/part1.csv.snappy
/data/in/files/sliced-file/subfolder/part2.csv.snappy

```
results in
```
/data/in/files/sliced-file/part1.csv.snappy/part1.csv
/data/in/files/sliced-file/part2.csv.snappy/part2.csv
/data/in/files/sliced-file/subfolder/part1.csv.snappy/part1.csv
/data/in/files/sliced-file/subfolder/part2.csv.snappy/part2.csv
```

# Development

Clone this repository and init the workspace with following command:

```
git clone https://github.com/keboola/processor-decompress
cd processor-decompress
docker-compose build
```

Run the test suite using this command:

```
docker-compose run dev composer ci
```

# Integration
 - Build is started after push on [Travis CI](https://travis-ci.org/keboola/processor-decompress)
 - [Build steps](https://github.com/keboola/processor-decompress/blob/master/.travis.yml)
   - build image
   - execute tests against new image
   - publish image to ECR if release is tagged
