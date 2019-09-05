import zlib
import sys

CHUNKSIZE = 1024
zlib_obj = zlib.decompressobj(int(sys.argv[2]))
compressed_file = open(sys.argv[1], 'rb')
buf = compressed_file.read(CHUNKSIZE)

decompressed_file = open(sys.argv[3], 'wb')

# Decompress stream chunks
while buf:
    decompressed_data = zlib_obj.decompress(buf)
    buf = compressed_file.read(CHUNKSIZE)
    decompressed_file.write(decompressed_data)

decompressed_file.write(zlib_obj.flush())

compressed_file.close()
decompressed_file.close()
