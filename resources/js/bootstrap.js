window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    // key: process.env.MIX_PUSHER_APP_KEY,
    // cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    // forceTLS: false,
    // authEndpoint: "/pusher/auth",
    auth: {
        headers: {
            "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTcyLjE2LjIxLjE2ODo4MDAwL2F1dGgvbG9naW4iLCJpYXQiOjE2ODk2NjIwMDYsImV4cCI6MTY4OTY3MjgwNiwibmJmIjoxNjg5NjYyMDA2LCJqdGkiOiJ6bWowN21vMEJ2MlR1WHp1Iiwic3ViIjoiMSIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.ac1Cogdj1DFh_f3z5dIi3DT7vEqdY45xXITgcYea1qo",
        },
    }
    // userAuthentication: {
    //     endpoint: "/pusher/user-auth",
    //     headers: {
    //         "X-CSRF-Token": "<%= form_authenticity_token %>",
    //         "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTcyLjE2LjIxLjE2ODo4MDAwL2F1dGgvbG9naW4iLCJpYXQiOjE2ODk2NDg2MzIsImV4cCI6MTY4OTY1OTQzMiwibmJmIjoxNjg5NjQ4NjMyLCJqdGkiOiI0UWZtTk9QM2ttcXp6YzBtIiwic3ViIjoiMSIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.h0SuG4uZNFKswewffaFPfR7AuNgUGVYn14ro9IZ5HXU",
    //      },
    //   },
});
