<?php

class DMS{

  /**
   * Singleton Instance
   * @var DMS
   */
  private static $instance;

  /**
   * Current HTTP_HOST
   * @var String
   */
  private $domain;

  /**
   * Map of Host/Post ID
   * @var String[int]
   */
  private $map  = array();

  /**
   * Singleton
   * @return DMS
   */
  public static function getInstance(){
    if( !isset( self::$instance ) )
      self::$instance  = new self();

    return self::$instance;
  }

  /**
   * Runs DMS, executed on WP init-Hook
   * @return void
   */
  public static function run( $query ){
    if( !is_admin() ){
      $DMS = self::getInstance();
      $DMS->generateMap();
      $DMS->setCurrentDomain();

      if( !empty( $DMS->map[$DMS->domain] ) ){
        parse_str( $DMS->map[$DMS->domain] , $a );
        $query->query_string = ( $query->query_string ? '&' : '' ).$DMS->map[$DMS->domain];
        $query->query_vars = array_merge( $query->query_vars , $a );
      }
    }
    return $query;
  }

  /**
   * Unregister WP settings, executed on Plugin deactivation
   * @return void
   */
  public static function deactivate(){
    unregister_setting( 'dms_config' , 'dms_map' );
    // Unregister other settings, yknow.
  }

  /**
   * Register DMS Settings and enqueue Scripts and Styles
   * @return void
   */
  public static function adminInit(){
    $DMS = self::getInstance();
    $DMS->registerSettings()->registerStyles();
  }

  /**
   * Adds Admin Options Page
   * @return void
   */
  public static function addOptionsMenu(){
    add_options_page(
      'Domain Mapping System Options',
      'DMS Options',
      'administrator',
      'dms-options',
      array(
        'DMS' ,
        'includeTemplate'
      )
    );
  }

  /**
   * Include DMS Option Template
   * @return void
   */
  public static function includeTemplate(){
    if( !current_user_can( 'manage_options' ) )
      wp_die( __( 'You do not have the permissions to access this page.' ) );
    include_once( plugin_dir_path(__FILE__) . '/templates/option-page.php' );
  }

  /**
   * Register WordPress Settings
   * @return $this
   */
  private function registerSettings(){
    register_setting( 'dms_config'  , 'dms_use' );
    register_setting( 'dms_storage' , 'map_domain' );
    register_setting( 'dms_storage' , 'map_target' );
    return $this;
  }

  /**
   * Register & enqueue CSS
   * @return $this
   */
  private function registerStyles(){
    wp_register_style('chosen-css', plugins_url('chosen.css', __FILE__), array(), '', 'all');
    wp_enqueue_style('chosen-css');
    wp_register_style('dms-css', plugins_url('dms.css', __FILE__), array(), '', 'all');
    wp_enqueue_style('dms-css');
    return $this;
  }

  /**
   * Register & enqueue JS
   * @return $this
   */
  public static function registerScripts(){
    wp_register_script('chosen-js', plugins_url('chosen.jquery.js', __FILE__), array('jquery'));
    wp_enqueue_script('chosen-js');
    wp_register_script('dms-js', plugins_url('dms.js', __FILE__), array('jquery'));
    wp_enqueue_script('dms-js');
    return $this;
  }

  /**
   * Generate Host/Post ID Map
   * @return String[int]
   */
  private function generateMap(){
    $domains = get_option( 'map_domain' , array() );
    $targets = get_option( 'map_target' , array() );
    $this->map = array_combine( $domains , $targets );
    return $this->map;
  }

  /**
   * Set current HTTP_HOST
   * @return String
   */
  private function setCurrentDomain(){
    $this->domain = $_SERVER['HTTP_HOST'];
    return $this->domain;
  }

  /**
   * DMS Magic
   *
   * Checks if current host is set to a certain post ID and corresponding post.
   *
   * @param mixed $pageID
   */
  private function map( $pageID ){

    if( is_numeric( $pageID ) ){
      /*
       * If $pageID is numeric, it is a Page, Post or CPT ID.
       * Thus, we configure the query_post arguments to the single object.
       */
      $postType  = get_post_type( $pageID );

      if( $postType!='page' )
      {
        $args  = array(
          'post_type' => $postType ,
          'p'         => $pageID
        );
      }
      else
      {
        $args = array(
          'page_id' => $pageID
        );
      }

      query_posts( $args );
    }elseif( is_numeric( strpos( $pageID , 'archive' ) ) ){
      $this->loadArchive( $pageID );
    }elseif( is_numeric( strpos( $pageID , 'category' ) ) ){
      $this->loadCategory( $pageID );
    }
  }

  /**
   * Query CPT posts & load (CTP) Archive Templates
   *
   * Includes archive-{$cpt-type}.php and terminates.
   * If archive-Template is not present, WordPress fallback
   * is used.
   *
   * @param String $pageID
   */
  private function loadArchive( $pageID ){
    $postType = substr( $pageID , 8 );

    $args  = array(
      'post_type'   => $postType ,
      'm'           => 0 ,
      'p'           => 0 ,
      'post_parent' => 0
    );

    query_posts( $args );

    /*
     * $file is the path for the CPT Archive Template
    */
    $file = TEMPLATEPATH.'/archive-'.$postType.'.php';

    /*
     * If a CPT Archive Template exists, use it and kill the script. Otherwise
    * let WordPress handle the fallback stuff.
    */
    if( file_exists( $file ) ){
      include_once( $file );
      exit( 0 );
    }
  }

  /**
   * Query Posts by Category, let WP handle the rest.
   *
   * @param unknown_type $pageID
   */
  private function loadCategory( $pageID ){
    $category  = substr( $pageID , 9 );

    $args  = array(
      'category_name' => $category
    );

    query_posts( $args );
  }

  /**
   * Get clean array of CPT
   * @return array
   */
  public static function getCustomPostTypes(){
    $types  = get_post_types(
      array(
        'public'   => true ,
        '_builtin' => false
      ) ,
      'objects'
    );

    $cleanTypes  = array();

    foreach( $types as $item ){
      $cleanTypes[]  = array(
        'name'        => $item->query_var ,
        'label'       => $item->labels->name ,
        'has_archive' => $item->has_archive
      );
    }

    return $cleanTypes;
  }

  /**
   * Compiles a clean list of DMS Options
   * @return array
   */
  public static function getDMSOptions(){
    $DMS     = self::getInstance();
    $posts   = array();
    $dms_use = get_option( 'dms_use' , array() );

    if( in_array( 'page' , $dms_use ) ){
      $pages = get_pages(
        array(
          'post_type' => 'page'
        )
      );
      if( !empty( $pages ) ){
        $posts['Pages'] = array();
        foreach( $pages as $page ){
          $posts['Pages'][] = array(
            'id'    => 'page_id='.$page->ID ,
            'title' => $page->post_title
          );
        }
      }
    }

    if( in_array( 'post' , $dms_use ) ){
      $blogPosts = get_posts(
        array(
          'numberposts' => -1
        )
      );
      if( !empty( $blogPosts ) ){
        $posts['Posts'] = array();
        foreach( $blogPosts as $post ){
          $posts['Posts'][] = array(
            'id'    => 'p='.$post->ID ,
            'title' => $post->post_title
          );
        }
      }
    }

    if( in_array( 'categories' , $dms_use ) ){
      $cats = get_categories();
      if( !empty( $cats ) ){
        $posts['Blog Categories'] = array();
        foreach ( $cats as $cat ){
          $posts['Blog Categories'][] = array(
            'id'    => 'category_name='.$cat->slug ,
            'title' => $cat->name
          );
        }
      }
    }

    $cleanTypes = self::getCustomPostTypes();
    if( !empty( $cleanTypes ) ){
      foreach( $cleanTypes as $type ){
        if( in_array( $type['name'] , $dms_use ) ){
          $args = array(
            'post_type'      => $type['name'] ,
            'posts_per_page' => -1
          );

          $loop = new WP_Query( $args );
          if( $loop->have_posts() ){
            $posts[$type['label']] = array();
            if( $type['has_archive'] ){
              $posts[$type['label']][] = array(
                'title' => $type['label'].' Archive' ,
                'id'    => 'archive-'.$type['name']
              );
            }
            while( $loop->have_posts() ){
              $loop->the_post();
              $posts[$type['label']][] = array(
                'title' => get_the_title() ,
                'id'    => get_the_ID()
              );
            }
          }
        }
      }
    }

    return $posts;
  }

}
