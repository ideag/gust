        <header id="global-header" class="navbar">
            <a class="ghost-logo" href="<?php bloginfo('url');?>" data-off-canvas="left" title="<?php bloginfo('title');?> &middot; <?php bloginfo('description');?>"><span class="hidden">wpGhost</span></a>
            <nav id="global-nav" role="navigation">
                <ul id="main-menu" >
<?php 
  $post_types = get_post_types(array('show_ui'=>true),'objects');
  //print_r($post_types);
  foreach ($post_types as $name=>$post_type) : 
    if ($name != 'attachment') :
  //print_r($post_type); ?>
                        <li class="content" id="menu-<?php echo $name; ?>"><a href="/ghost/<?php echo $name; ?>"><?php echo $post_type->labels->name; ?></a></li>
<?php endif; ?>                    
<?php endforeach; ?>                    
<!--                        <li class="content"><a href="/ghost/">Posts</a></li>
                        <li class="content"><a href="/ghost/pages">Pages</a></li>-->
                    
                        <li class="editor"><a href="/ghost/editor/post">New Post</a></li>
                    
<!-- TODO                        <li class="settings"><a href="/ghost/settings/">Settings</a></li>-->
                    

                    <li id="usermenu" class="subnav">
                        <a href="#" data-toggle="ul.user-menu" class="dropdown">
                            <img class="avatar" src="<?php echo get_avatar_url(get_current_user_id(),48); ?>" alt="Avatar" />
                            <span class="name"><?php $user = wp_get_current_user(); echo($user->data->display_name);?></span>
                        </a>
                        <ul class="user-menu overlay">
<!--                            <li class="usermenu-profile"><a href="/ghost/settings/user/">Your Profile</a></li>
                            <li class="divider"></li>
                            <li class="usermenu-help"><a href="http://ghost.org/forum/">Help / Support</a></li>
                            <li class="divider"></li>-->
                            <li class="usermenu-signout"><a href="/ghost/signout/">Sign Out</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </header>
