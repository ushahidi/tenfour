# API

TenFour has an API, it is versioned via headers, the default version being `v1`

## Authenticating

To authenticate with the API and do requests to it, you must first request a token:

    POST /api/auth HTTP/1.1
    Content-Type: application/json
    Host: api.tenfour.local

    {
    	"grant_type": "client_credentials",
    	"client_id": "1",
    	"client_secret": "T7913s89oGgJ478J73MRHoO2gcRRLQ"
    }
Once this is done you'll receive a token than you can then use for your requests via the `Authorization` header:

    GET /api/v1/users HTTP/1.1
    Authorization: Bearer THE_TOKEN
    Host: api.tenfour.local
