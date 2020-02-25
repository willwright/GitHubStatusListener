import base64
import json


def lambda_handler(event, context):
    username = "user"
    password = "password"
    auth_value_str = "{}:{}".format(username, password)
    auth_value_bytes = auth_value_str.encode('UTF-8')

    auth_string = "Basic " + str(base64.b64encode(auth_value_bytes), "UTF-8")

    print(event)

    # Require authentication
    headers = event["Records"][0]["cf"]["request"]["headers"]
    if "authorization" not in headers or headers["authorization"][0]["value"] != auth_string:
        print(auth_string)

        body = "Unauthorized"
        response = {
            "status": "401",
            "statusDescription": "Unauthorized",
            "body": body,
            "headers": {
                "www-authenticate": [{"key": "WWW-Authenticate", "value": "Basic"}]
            }
        }

        return response

    # Continue request processing if authentication passed
    return event["Records"][0]["cf"]["request"]
