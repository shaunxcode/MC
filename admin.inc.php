<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/smoothness/jquery-ui.css" type="text/css" media="all" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js"></script>
<script src="/ace/src/ace.js" type="text/javascript" charset="utf-8"></script>
<script src="/ace/src/theme-twilight.js" type="text/javascript" charset="utf-8"></script>
<script src="/ace/src/mode-javascript.js" type="text/javascript" charset="utf-8"></script>
<script src="/ace/src/mode-html.js" type="text/javascript" charset="utf-8"></script>
<script src="/ace/src/mode-php.js" type="text/javascript" charset="utf-8"></script>
<script src="/ace/src/mode-css.js" type="text/javascript" charset="utf-8"></script>

<script type="text/javascript">
<?php 
$ogpath = strtolower(isset($_GET['path']) ? $_GET['path'] : MC::$config->defaultRoute);
if(!isset(MC::$config->routes->$ogpath)) {
?>

$(function() {
    var combo = $('<select />');
    $.each(<?php echo json_encode(MC::getTemplates()) ?>, function(i, template) {
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
                    window.location.href = '/' + <?php echo json_encode(MC::$path) ?>;
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
        MC::$content->header = file_get_contents(MC::$dataDir . '/header.inc.php');
        MC::$content->footer = file_get_contents(MC::$dataDir . '/footer.inc.php');
    ?>
    var content = <?php echo json_encode(MC::$content) ?>; 
    var config = <?php echo json_encode(MC::$config) ?>;
    var path = <?php echo json_encode(MC::$path) ?>;
    var styles = {site: <?php echo json_encode(file_get_contents(MC::$dataDir . '/' . DIR_STYLE . 'site.css')) ?>};
    var template = <?php echo json_encode(file_get_contents(MC::$dataDir . '/' . DIR_TEMPLATE . MC::$config->routes->{MC::$path} . '.inc.php')); ?>;


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
                opacity: 0.2})
            .button({
                text: false,
                icons: {
                    primary: 'ui-icon-pencil'
                }
            })
            .appendTo('body')
            .hover(function(){
				$(this).css('opacity', 1);
                el.data('oldbg', el.css('background'));
                el.css('background', '#efefef');
            }, function(){
				$(this).css('opacity', 0.2);
                el.css('background', el.data('oldbg'));
            })
            .click(function() { 
                $('<div />')
                    .html($('<div />')
						.attr('id', 'editor')
						.css({position: 'relative', height: 455, width: 635})
						.text(content))
                    .dialog({
	                    width: 635,
	                    height: 580,
						resizable: false,
                        title: 'Edit ' + section,
						close: function() {
							$(this).remove();
						},
                        buttons: {
                            Cancel: function() {
                                $(this).dialog('close');
                            },
                            Save: function() { 
                                var newValue = editor.getSession().getValue();

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

				    var editor = ace.edit("editor");
				    editor.setTheme("ace/theme/twilight");
				    var HTMLMode = require("ace/mode/html").Mode;
				    editor.getSession().setMode(new HTMLMode());

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
		ul.append($('<div />').html($('<button />')
			.button({
				icons: {
					primary: 'ui-icon-plusthick'
				},
				text: false
			})
			.click(function() {
				alert('yeah dawg changd');
				return false;
			})))
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
		.append($('<button />').text('Page Config').button())
        .append($('<button />').text('Edit Template').button().click(function() {
            $('<div />').html($('<div />')
					.attr('id', 'editor')
					.css({position: 'relative', height: 455, width: 635})
					.text(template))
                .dialog({
                    title: 'Edit Template',
                    width: 635,
                    height: 580,
					resizable: false,
                    close: function() {
                        $(this).remove();
                    },
                    buttons: {
                        Cancel: function() {
                            $(this).dialog('close'); 
                        },
                        Save: function() {
                            $.post('/admin.php', {
                                template: <?php echo json_encode(MC::$config->routes->{MC::$path}) ?>, 
                                value: editor.getSession().getValue()
                            }, function(){
                                window.location.href = '/' + path;
                            });
                        }
                    }
                })

			    var editor = ace.edit("editor");
			    editor.setTheme("ace/theme/twilight");
				var PHPMode = require("ace/mode/php").Mode;
			    editor.getSession().setMode(new PHPMode());
        }))
        .append($('<button />').text('Edit Style').button().click(function() {
            $('<div />').html($('<div />')
					.attr('id', 'editor')
					.css({position: 'relative', height: 455, width: 635})
					.text(styles.site))
                .dialog({
                    title: 'Edit Style',
                    width: 635,
                    height: 580,
					resizable: false,
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
                                value: editor.getSession().getValue()
                            }, function(){
                                window.location.href = '/' + path;
                            });
                        }
                    }
                })

			    var editor = ace.edit("editor");
			    editor.setTheme("ace/theme/twilight");
				var CSSMode = require("ace/mode/css").Mode;
			    editor.getSession().setMode(new CSSMode());
			
        }))
		.append($('<button />').text('Edit Content').button()); 
})  
</script>
<style>
	.ui-dialog .ui-dialog-content {
		padding: 0;
	}

    #adminPanel button { display: block; margin-top: 5px; } 

	#adminPanel { 
		position: fixed; 
		top: 0; 
		right: 0; 
		padding: .5em; 
		background: #efefef; 
		border-left: 1px solid #ccc; 
		border-bottom: 1px solid #ccc; 
		opacity: 0.9;
		
		-webkit-border-bottom-left-radius: 5px;
		-moz-border-radius-bottomleft: 5px;
		border-bottom-left-radius: 5px;
	}
	
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
<div id="adminPanel"><a href="/admin.php?logout=true">logout</a><br /><br /></div>
