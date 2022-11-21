v2 Flow

V2 expect backend get the data on array['templates'] and Frontend load the templates with that data.



Template Data:
$templates['templates'][....]

Example:

..['templates'] = 
       '' => [
            name = 'user_prefs' //used for ref other inner templates
            tpl_file = "user_prefs" // Template file... user_prefs.tpl.php 
            tpl_pri = 9 //priority 0 last processed if this tpl data will be place in other tpl data this value must be greater than destine.
            tpl_vars = [ var = var] // tpl_key => tpl_value vars for this template
            tpl_place = 'admin_prefs_container' // when process the result will be add to home-item tpl_vars as this_var variable name
            tpl_place_var = 'this_var' or null // load that tpl in this var in tpl_place            
            tpl_place_replace = 1 // replace(=) default add (.) Probably not used never
            tpl_common_vars = array // when tpl_vars is array of arrays (multiple items) you can pass common vars to all this items adding here  (Frotend will be loop that items)
        ]
        ...
       '' => [
            name = 'admin_prefs_container'
            tpl_file = "home-item" // home-item.tpl.php top template
            tpl_pri = 8
            tpl_vars = [ this_var = [] ] // tpl_key => tpl_value
            tpl_place = 'homepage'
            tpl_place_var = 'col1' 
            
        ]
        ...
       '' => [
            name = 'homepage'
            tpl_file = "home-page" // home-item.tpl.php top template
            tpl_pri = 0
            tpl_vars = [ col1 = var, col2 = var] // tpl_key => tpl_value
            tpl_place = null
            tpl_place_var=  null // load that tpl in template_var 
            
        ]
]

When tpl_var & tpl_place_var is null, the frontend will load the template for echo 
Every page need at least one template with this fields null for echoing.

tpl_var can be array key => value or a array of arrays 
if the array is key => value the first never element can never be key => array
(to be fixed the method FrontEnd:if in 58) 


Min Template:
            $page['templates'][] = [
                'name' => 'msgbox',
                'tpl_file' => 'msgbox',
                'tpl_pri' => 4,
            ];

--------------------------------------------------------------------------------------
Msgbox Template

            $template['templates'][] = [
                'name' => 'msgbox',
                'tpl_file' => 'msgbox',
                'tpl_pri' => 4,
                'tpl_place' => 'view',
                'tpl_place_var' => 'extra',
                'tpl_vars' => [
                    'title' => $LNG['L_TORRENT'],
                    'body' => $LNG['L_NOTHING_FOUND'],
                ]
            ];