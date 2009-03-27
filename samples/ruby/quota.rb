require 'xuite/photo'

Xuite::Photo.public_key = '__PUT_YOUR_PUBLIC_KEY_HERE__'
Xuite::Photo.private_key = '__PUT_YOUR_PRIVATE_KEY_HERE__'

service = Xuite::Photo.get_service

# Get the auth token through the authentication process
token = '__PUT_THE_AUTH_TOKEN_HERE__'
quota = service.get_quota(token)

puts 'Used: ' + quota['used'].to_s + ' KB'
puts 'Quota: ' + quota['max'].to_s + ' KB'

