import base64
import json


def lambda_handler(event, context):
    print(event)

    # Forward Code to key off inside Firewall
    forward_header = {
        "x-forwarded-for": [{"key": "X-Forwarded-For", "value": "cloudfront"}]
    }

    request = event["Records"][0]["cf"]["request"]
    request["origin"]["custom"]["customHeaders"].update(forward_header)

    return request
