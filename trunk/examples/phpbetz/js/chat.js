var poll = document.location + '/poll'
var chat = $('#chat');
var message = $('#chatmessage');

message.focus();
chat.jScrollPane();
chat[0].scrollTo(chat.data('jScrollPaneMaxScroll'));

$.ajaxSetup({cache: false})

setTimeout(function() {
    $.get(poll, function(data) {
        if (data.length == 0) return;
        var scroll = chat.data('jScrollPanePosition') == chat.data('jScrollPaneMaxScroll');
        chat.append(data).jScrollPane();
        if (scroll) chat[0].scrollTo(chat.data('jScrollPaneMaxScroll'));
    }, 'text');
    setTimeout(arguments.callee, 1000);
}, 1000);

$('#chat-form').submit(
    function() {
        $.post(document.location, $(this).serialize());
        message.val('').focus();
        return false;
    }
);

$('.smiley').click(function() {
    message.val($.trim(message.val().rtrim() + ' ' + $(this).attr('title')) + ' ').focus();
    return false;
});

$('.smiley').qtip({
    show: 'mouseover',
    hide: 'mouseout',
    position: {
        corner: {
            target: 'topRight',
            tooltip: 'bottomLeft'
        }
    }
});