let log = function (msg) {
    $('#error').html('<div class="alert alert-danger" role="alert">' + msg + '</div>');
};

let urlParams = new URLSearchParams(window.location.search);
let room = urlParams.get('game');
let playerChar = '';
let field;
const conn = new WebSocket('ws://localhost:1337');

conn.onopen = function (e) {
    log("Connection established!");
};

conn.onmessage = function (e) {

    let data;
    $('#yourChecker')[0].innerHTML = this.playerChar;

    data = JSON.parse(e.data);
    joined = data.joined;
    playerChar = data.playerChar;
    field = data.field;
    win = data.win;
    error = data.error;
    info = data.info;

    if (joined) {
        log(joined);
        this.playerChar = playerChar
    }

    if (playerChar) {
        this.playerChar = playerChar;
        $('#join')[0].disabled = true;
        $('#new')[0].disabled = false;
    }

    if (field) {
        for (let key in field) {
            let value = field[key];

            if (value === 'undefined') {
                continue;
            }

            updateField(key, value);
        }
    }

    if (info) {
        log(info);
    }

    if (win) {
        log(win);
    }

    if (error) {
        log(error);
    }

};

conn.onerror = function (e) {
    log('Error occurred.');
    console.log(e);
};

$(window).on('load', function () {
    $('#title').html('Room ' + room);

});

$('#join').on('click', function () {

    conn.send(
        JSON.stringify(
            {
                method: 'join',
                roomId: room
            })
    );

});

$('#new').on('click', function () {

    conn.send(
        JSON.stringify(
            {
                method: 'new',
                roomId: room
            })
    );
});

$('#tictac tr td').on('click', function () {
    var self = $(this);

    if (self.hasClass('checked-' + playerChar)) {

        log('You already clicked ' + playerChar);

    } else {
        conn.send(
            JSON.stringify(
                {
                    method: 'check',
                    roomId: room,
                    field: self.parent('td').data('field'),
                    fieldNumber: self.data('id')
                }
            )
        );
    }

});

function updateField(field, value) {
    var self = $('#tictac tr td');
    var field = field-1;

    self[field].className = 'checked-' + value;
    self[field].innerHTML = value;
}