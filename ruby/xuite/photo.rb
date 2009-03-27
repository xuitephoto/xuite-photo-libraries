#
# = xuite/photo.rb - Xuite Photo Service Client
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
module Xuite
    class Photo
        private_class_method :new

        class APIInvokingError < StandardError; end

        SERVICE_ENDPOINT_URL = 'http://photo.xuite.net/_service/xmlrpc'

        @@public_key = ''
        @@private_key = ''
        @@instance = nil

        def Photo.get_service()
            @@instance = new unless @@instance
            @@instance
        end

        def Photo.create_signature(method_name, params = {})
            require 'digest/sha2'

            params['api_key'] = @@public_key if not params.include? 'api_key'
            params['method'] = method_name if not params.include? 'method'

            raw_string = @@private_key
            params.keys.sort.each do |key|
                raw_string << key + params[key].to_s
            end
            params['api_sig'] = Digest::SHA256.hexdigest(raw_string)
            params.delete('method')
        end

        def Photo.public_key=(key)
            @@public_key = key
        end

        def Photo.private_key=(key)
            @@private_key = key
        end

        def create_album(title = '', desc = '', auth_token = '')
            _invoke_method('xuite.photo.album.create', {
                'title' => title,
                'desc' => desc,
                'auth_token' => auth_token
            })
        end

        def get_frob()
            _invoke_method('xuite.photo.auth.getFrob')
        end

        def get_token(frob)
            _invoke_method('xuite.photo.auth.getToken', {'frob' => frob})
        end

        def get_albums(auth_token)
            _invoke_method('xuite.photo.user.getAlbums', {'auth_token' => auth_token})
        end

        def get_quota(auth_token)
            _invoke_method('xuite.photo.user.getQuota', {'auth_token' => auth_token})
        end

        private
        def _invoke_method(method_name, params = {})
            require 'xmlrpc/client'

            begin
                Photo.create_signature(method_name, params)
                xuite = XMLRPC::Client.new2(SERVICE_ENDPOINT_URL)
                xuite.call(method_name, params)
            rescue
                raise Photo.APIInvokingError.new
            end
        end
    end

end
