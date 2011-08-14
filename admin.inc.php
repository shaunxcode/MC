<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/smoothness/jquery-ui.css" type="text/css" media="all" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js"></script>
<script type="text/javascript">
<?php 
$ogpath = strtolower(isset($_GET['path']) ? $_GET['path'] : $config->defaultRoute);
if(!isset($config->routes->$ogpath)) {
    $templates = array();
    foreach(glob('../template/*.inc.php') as $template) {
        $last = explode('/', $template);
        $templates[] = current(explode('.', end($last))); 
    }
?>

$(function() {
    var combo = $('<select />');
    $.each(<?php echo json_encode($templates) ?>, function(i, template) {
        combo.append($('<option />').text(template).attr('value', template));
    }); 

    $('<div />')
        .html($('<label />').text('Template:')).append(combo)
        .dialog({
            title: 'Route "<?php echo $ogpath ?>" does not exist. Create?',
            modal: true,
            width: 600,
            buttons: {
                Cancel: function() {
                    window.location.href = '/' + <?php echo json_encode($path) ?>;
                },
                Create: function() {
                    $.post('/admin.php', {
                        newRoute: <?php echo json_encode($ogpath) ?>,
                        template: $('select', this).val()
                    }, function() {
                        window.location.href = '/' + <?php echo json_encode($ogpath) ?>;
                    }); 
                }
            }})
});
</script>
<?php die(); } ?>
$(function() {
    <?php 
        MC::$content->header = file_get_contents('../header.inc.php');
        MC::$content->footer = file_get_contents('../footer.inc.php');
    ?>
    var content = <?php echo json_encode(MC::$content) ?>; 
    var config = <?php echo json_encode($config) ?>;
    var path = <?php echo json_encode($path) ?>;
    var styles = {site: <?php echo json_encode(file_get_contents('style/site.css')) ?>};
    var template = <?php echo json_encode(file_get_contents('../template/' . $config->routes->$path . '.inc.php')); ?>;


    var relocateAdminButtons = function() {
        $('.MC_EditButton').each(function(i, but) {
            var pos = $(but).data('contentEl').offset();
            $(but).css({
                left: pos.left,
                top: pos.top});
        }); 
    };

    $(window).resize(relocateAdminButtons); 

    $.each(content, function(section, content) {
        var el = $('#' + section);
        if(el.length == 0) { 
            return;
        }

        $('<button />')
            .addClass('MC_EditButton')
            .data('contentEl', el)
            .css({
                position: 'absolute', 
                left: el.offset().left, 
                top: el.offset().top,
                opacity: 0.9})
            .button({
                text: false,
                icons: {
                    primary: 'ui-icon-pencil'
                }
            })
            .appendTo('body')
            .hover(function(){
                el.data('oldbg', el.css('background'));
                el.css('background', '#efefef');
            }, function(){
                el.css('background', el.data('oldbg'))
            })
            .click(function() { 
                $('<div />')
                    .html($('<textarea >').css({height: 455, width: '100%'}).val(content))
                    .dialog({
                        width: 800,
                        height: 600,
                        modal: true, 
                        title: 'Edit ' + section,
                        buttons: {
                            Cancel: function() {
                                $(this).dialog('close');
                            },
                            Save: function() { 
                                var newValue = $('textarea', this).val();

                                $.post('/admin.php', {
                                    path: path,
                                    section: section,
                                    value: newValue
                                }, function(result) {
                                    if(result == 'RELOAD') {
                                        window.location.href = '/' + path;
                                    } else {
                                        el.html(newValue);
                                        content = newValue;
                                        relocateAdminButtons(); 
                                    }
                                });
                                    
                                $(this).dialog('close');    
                            }
                        }
                    });
            });
    });


    var is_scalar = function(val) {
        return (/boolean|number|string/).test(typeof val);
    }

    var configEditor = function(config, namespace) { 
        var ul = $('<ul />');
        $.each(config, function(key, val) { 
            if(is_scalar(val)) { 
                ul.append($('<li />')
                    .html($('<span />').text(key))
                    .append($('<input />').attr({
                        type: 'text',
                        name: namespace + '[' + key + ']',
                        value: val
                    })));
            } else {
                ul.append($('<li />').html(key).append(configEditor(val, namespace + '[' + key + ']')));
            }
        });
        return ul;
    };
 
    $('#adminPanel')
        .append($('<button />').text('Site Config').button().click(function(){        
            var modal = $('<div />')
                .html($('<form />')
                    .addClass('AdminForm')
                    .html(configEditor(config, 'config')))
                .dialog({
                    modal: true,
                    title: 'Site Config' ,
                    width: 500,
                    close: function() {
                        modal.remove();
                    },

                    buttons: {
                        Cancel: function() {
                            $(this).dialog('close');
                        },
                        Save: function() {
                            $.post('/admin.php', $('form', this).serialize(), function() {
                                window.location.href = '/' + path;
                            });
                        }
                    }
                });
        }))
        .append($('<button />').text('Edit Template').button().click(function() {
            $('<div />').html($('<textarea />').css({width: '100%', height: 455}).val(template))
                .dialog({
                    modal: true,
                    title: 'Edit Template',
                    width: 800,
                    height: 600,
                    close: function() {
                        $(this).remove();
                    },
                    buttons: {
                        Cancel: function() {
                            $(this).dialog('close'); 
                        },
                        Save: function() {
                            $.post('/admin.php', {
                                template: <?php echo json_encode($config->routes->$path) ?>, 
                                value: $('textarea', this).val()
                            }, function(){
                                window.location.href = '/' + path;
                            });
                        }
                    }
                })

        }))
        .append($('<button />').text('Edit Style').button().click(function() {
            $('<div />').html($('<textarea />').css({width: '100%', height: 455}).val(styles.site))
                .dialog({
                    modal: true,
                    title: 'Edit Style',
                    width: 800,
                    height: 600,
                    close: function() {
                        $(this).remove();
                    },
                    buttons: {
                        Cancel: function() {
                            $(this).dialog('close'); 
                        },
                        Save: function() {
                            $.post('/admin.php', {
                                stylesheet: 'site', 
                                value: $('textarea', this).val()
                            }, function(){
                                window.location.href = '/' + path;
                            });
                        }
                    }
                })
        })); 
})  
</script>
<style>
    #adminPanel button { display: block; margin-top: 5px; } 

    .AdminForm ul { list-style-type: none; } 

    .AdminForm li > span {
        overflow: hidden;
        display: inline-block;
        float: left;   
  }

    .AdminForm li >span:after { content: ': '; }
    .AdminForm li { 
        margin-top: 5px;
    }

    .AdminForm li input { 
        border: 0; 
        padding: 0; 
        color: #999; 
        font-size: 90%;
        margin-left: 5px;
        display: inline-block;
    } 
</style> 
<div id="adminPanel" style="position: fixed; top: 0; right: 0; padding: .5em; background: #efefef; border-left: 1px solid #999; border-bottom: 1px solid #999; opacity: 0.9;"><a href="/admin.php?logout=true">logout</a><br /><br /></div>