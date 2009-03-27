#!/usr/bin/env python

from xuite.photo import XuitePhotoService

def main():
    service = XuitePhotoService(public_key='__PUT_YOUR_PUBLIC_KEY_HERE__',
                                private_key='__PUT_YOUR_PRIVATE_KEY_HERE__')
    # Get auth token through the authenticate process
    token = '__PUT_YOUR_AUTH_TOKEN_HERE__'
    quota = service.get_quota(token)

    print 'Used: %s KB' % quota['used']
    print 'Quota: %s KB' % quota['max']

if __name__ == '__main__':
    main()
