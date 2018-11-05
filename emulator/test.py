
import base64
import sys
def main(argv):
    str1 = "Ne4FQgEBBQo4NjIxMTgwMjUzMDE3MDM="
    decoded = base64.b64decode(str1)
    print (decoded)

    str2 = "NQAAAAoBFB44NjIxMTgwMjUzMDE3MDM="
    decoded = base64.b64decode(str2)
    print (decoded)

    str2 = "MDg2MjExODAyNTMwMTcwMw=="
    decoded = base64.b64decode(str2)
    print (decoded)



if __name__ == "__main__":
    main(sys.argv[1:])
