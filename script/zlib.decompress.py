import zlib
import sys
import os

CHUNKSIZE = 1024

if len(sys.argv) != 4:
    print("Invalid arguments supplied 3 arguments expected")
    exit(1)

if not os.path.isfile(sys.argv[1]):
    print("File {0} not exists".format(sys.argv[1]))
    exit(1)

zlib_obj = zlib.decompressobj(int(sys.argv[2]))

try:
  with open(sys.argv[3], 'wb') as decompressed_file:
      with open(sys.argv[1], 'rb') as compressed_file:
          buf = compressed_file.read(CHUNKSIZE)
          while buf:
              decompressed_data = zlib_obj.decompress(buf)
              buf = compressed_file.read(CHUNKSIZE)
              decompressed_file.write(decompressed_data)
          decompressed_file.write(zlib_obj.flush())
except:
  print("Chyba při dekompresi souboru {0} not exists".format(sys.argv[1]))
