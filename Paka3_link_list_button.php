<?php
/*
Plugin Name: paka3_link_list_button
Plugin URI: http://www.paka3.com/wpplugin
Description: 投稿画面に過去記事からのリンクリストを生成する。
Author: Shoji ENDO
Version: 0.1
Author URI:http://www.paka3.com/
*/


$p3EMB= new Paka3_Link_List_Button( );
//$p3EMB->my_action_callback();

class Paka3_Link_List_Button
{
	public function __construct(){
		add_filter( "media_buttons_context" , array( &$this, "paka3_media_buttons_context" ) );
		//ポップアップウィンドウ
		//media_upload_{ $type }
		add_action('media_upload_paka3Type', array( &$this,'paka3_wp_iframe' ) );
		//クラス内のメソッドを呼び出す場合はこんな感じ。
		add_action( "admin_head-media-upload-popup", array( &$this, "paka3_head" ) );

		if( is_admin() ){
			//*ログインユーザ   
			add_action('wp_ajax_paka3_pgp_action',array($this,'my_action_callback'));
		}

	}


	public function paka3_head(){
		global $type;
		if( $type == "paka3Type" ){

		//ポップアップで使うjavascript
		wp_enqueue_script( 'paka3_popup', plugin_dir_url( __FILE__ ) . '/js/paka3_popup.js', array( 'jquery' ));

		//既存記事を取得するajaxで使うjavascript
		wp_enqueue_script( 'paka3_submit', plugin_dir_url( __FILE__ ) . '/js/paka3_post.js', array( 'jquery' ));	
		wp_localize_script( 'paka3_submit', 'paka3Posts', array(
          'ajaxurl'       => admin_url( 'admin-ajax.php' ),
          'security'      => wp_create_nonce( get_bloginfo('url').'paka3PopGetPosts' ))
      );


		echo <<< EOS
			<style type="text/css">
			div.resblock{
				border:1px solid #eee;
				min-height:100pt;
				max-height:200pt;
				overflow:auto;
			}
			div#preview{
				border:1px solid #eee;
				padding:10pt;
				min-height:50pt;
				
			}
					div#preview ul{
						margin-top:5pt;margin-bottom:5pt;
						padding-top:5pt;padding-bottom:5pt;
						margin-left:10pt;padding-left:10pt;
					}
					div#preview li{
						list-style-type: disc!important;
					}

			</style>
EOS;
		}
	}

	//##########################
	//メディアボタンの表示
	//##########################
	public function paka3_media_buttons_context ( $context ) {
		$img = plugin_dir_url( __FILE__ ) ."icon.png";
		$link = "media-upload.php?tab=paka3Tab&type=paka3Type&TB_iframe=true&width=600&height=400";

		$context .= <<<EOS
    <a href='{$link}'
    class='thickbox' title='タイトルリンクを挿入するぜ'>
      <img src='{$img}' /></a>
EOS;
		return $context;
	}


	//##########################
	//ポップアップウィンドウ
	//##########################
	function paka3_wp_iframe() {
		wp_iframe(array( $this , 'media_upload_paka3_form' ) );
	}

	//関数名をmedia_***としないとスタイルシートが適用されない謎
	function media_upload_paka3_form() {
		add_filter( "media_upload_tabs", array( &$this, "paka3_upload_tabs" ) ,1000);
		media_upload_header();

		$dirUrl = plugin_dir_url( __FILE__ );
		echo <<< EOS
			<div id="paka3_popup_window" style="background:#fff">
			<form  action="">
				<h2>記事タイトルのリンクリストを挿入</h2>
				<p>
				<input type="checkbox" name="opt_li" class='pgp_chk' id=opt_li >リスト表示にする(ul)
				<div id ="preview"></div>
				</p>
				<input type="button" value="選択したリンクを挿入する" id="paka3_ei_btn_yes" class="button button-primary" /> 
				<input type="button" value="キャンセル" id="paka3_ei_btn_no"  class="button" />
			
			<!-- ここに表示 -->
			<div class="resblock">
				<ul id="res"></ul>
				<input type=hidden id=paka3getpost_count value = "0" />
				<!-- このポイントで読み込み -->
				<div id=loadingmessage><img src="{$dirUrl}/loadimg.gif" /></div>
				<div class="paka3_trigger"></div>
			</div>

			<button type="button" class="button" id="getPostsSubmit">続きを読み込む</button>
			<button type="button" class="button" id="re_getPostsSubmit">再読み込み</button>
			
			

			</form>
EOS;
	}

	//##########################
	//ポップアップウィンドウのタブ
	//##########################
	function paka3_upload_tabs( $tabs )
	{
		$tabs = array();
		$tabs[ "paka3Tab" ] = "タイトルリンクリストの挿入" ;
		return $tabs;
	}




	//##################################
	//Ajaxコールバック関数
	//##################################
	public function my_action_callback(){
		if( isset($_POST['paka3getpost_count']) && check_admin_referer( get_bloginfo('url').'paka3PopGetPosts','security')){
				$this->args = array(
								'numberposts'   => 10,
								'offset'           => $_POST['paka3getpost_count'] * 10 ,
								'category'         => '',
								'orderby'          => 'post_date',
								'order'            => 'DESC',
								'post_type'        => 'post',
								'post_status'      => 'publish'); 

				//記事の取得
				$posts_array = get_posts( $this->args );
				$a = array();
				//記事データの整形
				foreach ( $posts_array as $akey => $aval) {
					$a[ $akey ] = $aval ; 
					//静的リンクを追加する。
					$a[ $akey ]->post_href = get_permalink( $aval->ID );
					$a[ $akey ]->post_edit_href = get_edit_post_link( $aval->ID );
				}
				//JSON出力
				$response = json_encode( $a );
				header( "Content-Type: application/json" );
				echo $response;
				exit;
		}else{
			die("エラー");
		}
	}
}











  



