# -*- coding: utf-8 -*-
#
# Copyright 2009 Xuite Photo
#
# Permission is hereby granted, free of charge, to any person
# obtaining a copy of this software and associated documentation
# files (the "Software"), to deal in the Software without
# restriction, including without limitation the rights to use,
# copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the
# Software is furnished to do so, subject to the following
# conditions:
# 
# The above copyright notice and this permission notice shall be
# included in all copies or substantial portions of the Software.
# 
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
# EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
# OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
# NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
# HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
# WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
# FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
# OTHER DEALINGS IN THE SOFTWARE.
#
 
""" The xuite photo package. """
 
SERVICE_ENDPOINT_URL = 'http://photo.xuite.net/_service/xmlrpc'
 
def create_signature(method_name, params, **key):
    """
        create_signature is used to hash the parameters (and method name)
        to be an API signature for API invoking.
 
        This method will modify the content of params
    """
    import hashlib
 
    public_key = key.get('public_key', '')
    private_key = key.get('private_key', '')
 
    if not params.has_key('api_key'):
        params['api_key'] = public_key
 
    if not params.has_key('method'):
        params['method'] = method_name
 
    # TODO: sort and combine
    sorted_keys = sorted(params.keys())
    raw = [private_key]
    for k in sorted_keys:
        raw.append('%s%s' % (k, params[k]))
    hash = hashlib.sha256()
    hash.update(''.join(raw))
    params['api_sig'] = hash.hexdigest()
 
    del params['method']
 
 
class ServiceMethodNotFoundError(Exception):
    def __init__(self, message):
        self.message = message
 
    def __str__(self):
        return repr(self.message)
 
 
class XuitePhotoService(object):
    class __impl:
        """
        The real implementation of XuitePhoto Service
        """
        def __init__(self, **keys):
            self.public_key = keys.get('public_key')
            self.private_key = keys.get('private_key')
 
        def create_album(self, auth_token, **args):
            """
                The wrapper of xuite.photo.album.create
            """
            title = args.get('title', '')
            desc = args.get('desc', '')
 
            params = {'auth_token': auth_token}
            params.update({
                'title': title,
                'desc': desc
            })
 
            return self._invoke('xuite.photo.album.create', params)
            
 
        def get_frob(self):
            """
                The wrapper of xuite.photo.auth.getFrob
            """
            return self._invoke('xuite.photo.auth.getFrob', {})
 
        def get_token(self, frob):
            """
                The wrapper of xuite.photo.auth.getToken
            """
            return self._invoke('xuite.photo.auth.getToken', {'frob': frob})
 
        def get_albums(self, auth_token):
            """
                The wrapper of xuite.photo.user.getAlbums
            """
            return self._invoke('xuite.photo.user.getAlbums', {'auth_token': auth_token})
 
        def get_quota(self, auth_token):
            """
                The wrapper of xuite.photo.user.getQuota
            """
            return self._invoke('xuite.photo.user.getQuota', {'auth_token': auth_token})
 
        def _invoke(self, method_name, params):
            import xmlrpclib
 
            create_signature(method_name, params, 
                            public_key=self.public_key, private_key=self.private_key)
            xmlrpc_proxy = xmlrpclib.ServerProxy(SERVICE_ENDPOINT_URL, encoding='utf-8')
            print params
            response = None
            try:
                # invoking the api method
                response = getattr(xmlrpc_proxy, method_name)(params)
            except AttributeError: # method not found
                raise ServiceMethodNotFoundError('%s: method not found' % method_name)
 
            return response
 
    __instance = None
 
    def __init__(self, **keys):
        """
        Singleton method.
        """
        public_key = keys.get('public_key')
        private_key = keys.get('private_key')
        if XuitePhotoService.__instance is None:
            XuitePhotoService.__instance = XuitePhotoService.__impl(public_key=public_key,
                                                                    private_key=private_key)
        self.__dict__['_XuitePhotoService__instance'] = XuitePhotoService.__instance
 
    def __getattr__(self, attr):
        return getattr(self.__instance, attr)
 
    def __setattr__(self, attr, value):
        return setattr(self.__instance, attr, value)
