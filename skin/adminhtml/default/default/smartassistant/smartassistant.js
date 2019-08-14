var SmartassistantMap = {
    up: function(e) {
        SmartassistantMap.move(e, 'up');
    },
    down: function(e) {
        SmartassistantMap.move(e, 'down');
    },
    remove: function(e) {
        e.ancestors()[1].remove();
    },
    add: function(e) {
        var trNode = $$('#mapping-table tfoot tr').first().cloneNode(true);
        trNode.toggleClassName('hidden-row');

        var n=Math.floor(Math.random()*11);
        var k = Math.floor(Math.random()* 1000000);
        var unique = String.fromCharCode(n) + k;

        var fieldNameNode = $(trNode).select('.fieldname-entry')[0];
        $(fieldNameNode).addClassName("required-entry");
        $(fieldNameNode).setAttribute('id', unique);

        $$('#mapping-table tbody').last().insert(trNode);
    },
    move: function (e, direction) {
        var row = e.ancestors()[1];
        var table = row.parentNode;

        index = table.select('tr').indexOf(row);

        var prev = ((index > 0) ? index - 1 : 1);
        var next = table.select('tr').length - 2;

        if (index < table.select('tr').length - 1) {
            next = index + 1;
        }

        prevli = table.select('tr')[prev];
        nextli = table.select('tr')[next];

        row.remove();

        if (direction == 'up') {
            prevli.insert({before : row});
        } else if (direction == 'down') {
            nextli.insert({after : row});
        }
    }
};

var SmartassistantStatus = {
    'content' : null,
    'loader' : null,
    'interval' : 5000,
    'time' : null,
    'start' : function(url) {
        SmartassistantStatus.loader = $('smartassistant_loader');
        if (SmartassistantStatus.loader) {
            SmartassistantStatus.loader.show();
        }
        SmartassistantStatus.update(url);
    },
    'update' : function(url) {
        new Ajax.Request(url, {
            method: 'get',
            onCreate: function(request) {
                Ajax.Responders.unregister(varienLoaderHandler.handler);
            },
            onSuccess: function(transport) {
                $('task-stats-container').replace(transport.responseJSON.html);
                if (! transport.responseJSON.finished) {
                    SmartassistantStatus.time = setTimeout(function() {
                        SmartassistantStatus.update(url);
                    }, SmartassistantStatus.interval);
                } else {
                    $('loading_mask_loader').remove();
                }
            }
        });
    },
    'stop' : function() {
        SmartassistantStatus.loader.hide();
        clearTimeout(SmartassistantStatus.time);
    }
};

