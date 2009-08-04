<?php
    /**
     * @file   jp.lang.php
     * @author sol (sol@ngleader.com) 翻訳：ミニミ
     * @brief  Textyle(textyle) モジュールの日本語言語パッケージ
     **/

    $lang->textyle = 'Textyle';
    $lang->about_textyle = 'TextyleはXpressEngineのブログ専用モジュールです。';

	$lang->msg_create_textyle = 'Textyleが作成されました。';
	$lang->init_category_title = '基本カテゴリー';
	$lang->add_category = 'カテゴリー追加';
    $lang->textyle_admin = 'Textyle管理者(ID)';
    $lang->textyle_title = 'Textyleタイトル';
    $lang->today_visitor = '本日の<strong>訪問ユーザー数</strong>';
    $lang->today_comments = '本日の<strong>コメント数</strong>';
    $lang->today_trackbacks = '本日の<strong>トラックバック/ピングバック数</strong>';
    $lang->textyle_summary = 'ブログ情報';
    $lang->item_unit = '個';
    $lang->newest_documents ='最近作成記事';
    $lang->newest_no_documents ='作成した記事がありません。新しい記事を投稿して下さい。';
    $lang->newest_materials = '最近文材';
	$lang->newest_no_materials = '文材がありません。 <a href="%s">文材収集機をインストールして下さい。</a>';
	$lang->newest_comments = '最近コメント';
	$lang->newest_no_comments = '登録されたコメントがありません。';
	$lang->newest_guestbooks = '最近ゲストブック';
	$lang->newest_no_guestbooks = 'ゲストブックに登録された書き込みがありません。';
	$lang->reserve = '予約';
    $lang->publish = '発行';
    $lang->temp = '下書き';
    $lang->more = 'もっと見る';
    $lang->last_week = '先週';
    $lang->this_week = '今週';
    $lang->display_name = '表示名';
    $lang->about_display_name = '未作成の場合、IDを表示します。';
    $lang->about_email = '未作成の場合、パスワードリマインダーからのメール送信が出来ません。';
    $lang->no_profile_image = 'プロフィル写真がありません。';
    $lang->allow_profile_image_type = '<strong>jpg, gif, png</strong>ファイルアップロード可能';
    $lang->allow_profile_image_size = 'イメージサイズは<strong>%dx%d</strong>pxに自動リサイズ';
    $lang->signature = '自己紹介';
    $lang->default_config = '基本設定';
    $lang->blog_title = 'ブログタイトル';
    $lang->about_blog_title = 'ブログのタイトルとして使います。';
    $lang->blog_description = '簡単紹介';
    $lang->about_blog_description = 'スキン種類によって、表示されるかHTML&middot;CSSエディターにて表示可否を決定します。';
    $lang->favicon = 'ファビコン';
    $lang->registed_favicon = '登録されたファビコン';
    $lang->about_favicon = '<strong>16x16px</strong>サイズの<strong>ico</strong>ファイルアップロード可能';
    $lang->about_addon = 'アドオンは、HTMLの出力をコントロールすると言うより、動作を制御する役割をします。お好みのアドオンを「使用/未使用」に設定するだけで、サイトの運営に有用な機能が利用出来ます。';
    $lang->lang_time_zone = '言語/時間帯';
    $lang->language = '言語';
    $lang->timezone = '時間帯';
    $lang->edit_style = '編集方法選択';
    $lang->textyle_editor = '段落別エディター';
    $lang->font_style = 'フォント設定';
    $lang->font_family = 'フォントタイプ';
    $lang->font_size = 'フォントサイズ';
    $lang->about_font_family = '編集と発行時、内容の基本フォトを定めます。 （例： %s）';
    $lang->font_family_list = array(
		'ＭＳ Ｐゴシック, MS PGothic'=> 'ＭＳ Ｐゴシック',
		'ＭＳ Ｐ明朝, MS PMincho'=> 'ＭＳ Ｐ明朝',
		'MS UI Gothic, MS UI Gothic'=> 'MS UI Gothic',
		'Sans-serif, Sans-serif'=> 'Sans-serif',
		'Serif, Serif'=> 'Serif',
		'Verdana, Verdana'=> 'Verdana'
	);
    $lang->about_font_size = '編集と発行時、内容の基本フォトサイズを定めます。 (例： 12px、または1emなどの単位を含む) ';
    $lang->about_textyle_editor = '段落別編集方式のエディターです。';
    $lang->etc_editor = 'その他のエディター';
    $lang->about_etc_editor = '一般的なリッチテキストエディターです。';
    $lang->set_prefix = 'ヘッダー設定';
    $lang->about_prefix = '作成した記事へ下の内容を自動挿入します。 (HTMLタグ可能)';
    $lang->set_suffix = 'フッター設定';
    $lang->about_suffix = '作成した記事へ下の内容を自動挿入します。 (HTMLタグ可能)';
    $lang->blogapi = '遠隔投稿';
    $lang->blogapi_support = 'Blog API(Meta Weblog API)を利用して、遠隔投稿が出来ます。';
    $lang->blogapi_example = '例) Window Live Writer、 Google Docs、 MS Word 2007 など';
    $lang->blogapi_url = 'APIのURL';
    $lang->blog_first_page = '最初のページ';
    $lang->blog_display_target = '出力対象';
    $lang->content_body = 'コンテンツ本文';
    $lang->content_summary = 'コンテンツ要約';
    $lang->content_list = 'コンテンツリスト';
    $lang->blog_display_count = 'リスト表示数';
    $lang->rss_type = '発行対象';
    $lang->rss_total = 'タイトル + 本文全体';
    $lang->rss_summary = 'タイトル + 本文要約';
    $lang->visitor_editor_style = 'コメント、およびゲストブックの入力スタイル';
    $lang->host = '参照サイト';
    $lang->referer = 'リファラー';
    $lang->link_word = 'リンクワード';
    $lang->link_document = 'リンクドキュメント';
    $lang->visitor = '訪問ユーザー';
    $lang->about_referer = '訪問ユーザーのリファラー確認が出来ます。';
    $lang->website_address = 'ウェブサイトアドレス';
    $lang->required = '必須';
    $lang->selected = '選択';
    $lang->notaccepted = '受け付けない';
    $lang->comment_editor = 'コメントエディター';
    $lang->guestbook_editor = 'ゲストブックエディター';
    $lang->comment_list_count = 'ページあたりのコメント数';
    $lang->guestbook_list_count = 'ページあたりのゲストブック数';
    $lang->comment_grant = 'コメント作成権限';
    $lang->about_comment_grant = 'コメント作成権限を会員のみ、もしくはすべての訪問者へも付与出来ます。';
    $lang->disable_comment= 'コメント作成権限がありません。会員登録後、作成が可能となります。';
    $lang->grant_to_all = '全て';
    $lang->grant_to_member = '会員';
    $lang->guestbook_grant = 'ゲストブック作成権限';
    $lang->about_guestbook_grant = 'ゲストブック権限を会員のみ、もしくはすべての訪問者へも付与出来ます。';
    $lang->disable_guestbook = 'ゲストブック作成権限がありません。会員登録後、作成が可能となります。';
    $lang->current_password = '現在パスワード';
    $lang->password1 = '新しいパスワード';
    $lang->password2 = '新しいパスワードの確認';
    $lang->about_change_password = '上に入力した新しいパスワードと同一のものを入力して下さい。';
    $lang->name_nick = 'お名前(ニックネーム)';
    $lang->manage_trackback ='トラックバック管理';
    $lang->manage_guestbook ='ゲストブック管理';
    $lang->manage_comment ='コメント管理';
    $lang->document_list ='記事リスト';
    $lang->type = 'タイプ';
    $lang->trackback_site = 'トラックバック/サイト';
    $lang->total_result_count = '総<strong>%d</strong>個の記事があります。';
    $lang->search_result_count = '検索結果<strong>%d</strong>個の記事があります。';
    $lang->no_result_count = '検索結果がありません。';
    $lang->selected_articles = '選択したものを';
    $lang->avatar = 'アバター';
    $lang->status = '状態';
    $lang->pingback = 'ピングバック';
    $lang->recent_tags = '最近使ったタグ';
    $lang->total_tag_result = '総<strong>%d</strong>個のタグを使っています。';
    $lang->align_by_char = '昇順並べ替え';
    $lang->used_documents = '使われた記事数';
    $lang->order_desc = '降順並べ替え';
    $lang->update_tag = 'タグ修正/削除';
    $lang->tag_name = 'タグ名';
    $lang->tag_with_tags = '一緒に使われたタグ';
    $lang->total_materials = '総 <strong>%d</strong>個の文材が保存されています。';
    $lang->none_materials = '文材は有りません。';
    $lang->install_bookmarklet = '文材収集機をインストールして下さい。';
    $lang->none_tags = 'タグはありません。';
    $lang->bookmarklet_install = 'ブックマークレットをインストールする';
	$lang->about_bookmarklet = 'いつも、とこでも<br />ブログに書く文材を収集して下さい。';
    $lang->about_set_bookmarklet = '文材収集機';
    $lang->data_export = 'データバックアップ';
    $lang->data_import = 'データリストア (以前)';
    $lang->migration_prepare = 'ファイル分析中';
    $lang->data_import_progress = 'バックアップ進行率';
    $lang->data_export_progress = 'リストア進行率';
    $lang->about_export_xexml = 'XpressEngine専用のXE XML形式として保存されたファイルを用いたデータの移管が可能です。';
    $lang->about_export_ttxml = 'Textcube専用のTTXML形式として保存されたファイルを用いたデータの移管が可能です。';
    $lang->migration_file_path = 'XMLファイル位置(URL、もしくはパス)';
    $lang->msg_migration_file_is_null = 'リストアするXMLファイルの位置を入力して下さい。';
    $lang->cmd_import = 'インポートする';
    $lang->send_me2 = 'me2Dayへ投稿';
    $lang->about_send_me2 = '記事投稿時、タイトルを指定したme2Dayへ投稿出来るように設定します。';
    $lang->me2day_userid = 'me2DayのID';
    $lang->about_me2day_userid = '"http://me2day.net/ID"のID値を入力して下さい。';
    $lang->me2day_userkey = 'me2Dayの利用者キー';
    $lang->about_me2day_userkey = 'me2Dayの環境設定に表示されている利用者キーを入力して下さい。';
    $lang->check_me2day_info = '連結確認';
    $lang->msg_success_to_me2day = '入力したme2Day情報で連結確認が正常に完了しました。';
    $lang->msg_fail_to_me2day = 'me2Dayへ連結が失敗しました。もう一度、IDと利用者キーを確認してください。';

    $lang->send_twitter = 'ツイッター発行';
    $lang->about_send_twitter = '記事投稿時、タイトルを指定したツイッターへ発行出来るように設定します。';
    $lang->twitter_userid = 'ツイッターID';
    $lang->about_twitter_userid = 'ツイッターIDを入力して下さい。';
    $lang->twitter_password = 'ツイッターパスワード';
    $lang->about_twitter_password = 'ツイッターパスワードを入力して下さい。';

    $lang->blogapi_publish = 'BlogAPI発行';
    $lang->about_blog_api = 'テキスタイルにて作成した記事をBlogAPIにて他ブログ、または掲示板などに同時投稿/修正/削除が可能です。<br/>サポートするBlogAPIはMetaWebLogのみで、他のAPIは準備中です。';
    $lang->cmd_registration_blogapi = 'BlogAPIサイト登録';
    $lang->cmd_modification_blogapi = 'BlogAPIサイト情報修正';
    $lang->blogapi_site_url = 'API対象サイト';
    $lang->about_blogapi_site_url = 'BlogAPIにて投稿する対象サイトのアドレスを入力して下さい。';
    $lang->blogapi_site_title = 'サイトタイトル';
    $lang->about_blogapi_site_title = 'BlogAPIにて投稿するサイトタイトルを決めます。';
    $lang->blogapi_api_url = 'API URL';
    $lang->about_blogapi_url = 'BlogAPI URLを入力して下さい。';
    $lang->blogapi_published = '投稿数';
    $lang->blogapi_user_id = 'ユーザーID';
    $lang->about_blogapi_user_id = 'API対象サイトで使われるユーザーIDを入力して下さい。';
    $lang->blogapi_password = 'ユーザーパスワード';
    $lang->about_blogapi_password = 'API対象サイトで使われるユーザーパスワードを入力して下さい。';
    $lang->cmd_get_site_info = 'サイト情報の取得';
    $lang->cmd_check_api_connect = 'API連結確認';
    $lang->msg_url_is_invalid = "入力したURLにアクセス出来ません。\n\nもう一度URLを確認して下さい。";
    $lang->msg_remove_api = 'API情報を削除しますか？';
    $lang->msg_blogapi_registration = array(
        'API対象サイトアドレスを入力して下さい。',
        'サイトタイトルを入力して下さい。',
        'APIアドレスを入力して下さい。',
        'ユーザーIDを入力して下さい。',
        'ユーザーパスワードを入力して下さい。',
    );

    $lang->about_use_bookmarklet = 'ブックマークレットのヘルプ';
    $lang->about_use_bookmarklet_item = array(
        '上の「文材収集機」ブックマークレットをブラウザーに追加（インストール）して下さい。',
        '記事を書く時、素材として使えるものを目付けたら、インストールしたブックマークレットをクリックして下さい。',
        'テキスト、イメージ、動画など、適切なタイプを選んで編集した後保存して下さい。',
        'そうして収集した文材はただ今ご覧になっている文材保存箱ページに保存されます。',
    );
    $lang->basket_management = 'ごみ箱の管理';
    $lang->basket_list = 'ごみ箱の保管リスト';
    $lang->basket_empty = 'ごみ箱が空です。 ^^;';

    $lang->document_all = '全ての記事を見る';
    $lang->document_published = '発行された記事のみ見る';
    $lang->document_reserved = '下書きの記事のみ見る';

    $lang->my_document_management = 'マイ記事管理';
    $lang->set_publish = '公開設定';
    $lang->document_open = '公開';
    $lang->document_close = '非公開';

    $lang->category = 'カテゴリー';
    $lang->comm_management = 'コミュニケーション設定';
    $lang->allow_comment = 'コメント許可';
    $lang->allow_trackback = 'トラックバック許可';
    $lang->publish_date = '発行時刻';
    $lang->publish_now = '現在';
    $lang->publish_reserve = '予約';
    $lang->ymd = '年.月.日';
    $lang->calendar = 'カレンダー';
    $lang->close_calendar_layer = 'カレンダーを閉じる';
    $lang->select_calendar_layer = 'カレンダーから選択';

    $lang->insert_title = 'タイトルを入力して下さい。';
    $lang->new_post = '記事新規作成';
    $lang->modify_post = '記事を修正する';
    $lang->posting_option = '書き込みのオプション';
    $lang->post_url = 'ポストアドレス';
    $lang->about_tag = '複数のタグは半角コンマ「,」で区切ってください。';
	$lang->success_temp_saved = '下書きとして臨時保存されました。';

    $lang->daily = '日別';
    $lang->weekly = '週間';
    $lang->monthly = '月間';
    $lang->before_day = '一日前';
    $lang->after_day = '一日後';
    $lang->before_month = '先月';
    $lang->after_month = '次月';
    $lang->this_month = '前月';
    $lang->today = '今日';
    $lang->day_current = '当日';
    $lang->day_before = '前日';
    $lang->about_status = array(
        'day'=>'時間帯別訪問数が確認出来ます。',
        'week'=>'一週間・日付別訪問数が確認出来ます。',
        'month'=>'一年間・月別訪問数が確認出来ます。',
    );
    $lang->about_unit = array(
        'day'=>'時間',
        'week'=>'年.月.日',
        'month'=>'年.月',
    );
    $lang->visit_count = '訪問数';
    $lang->visit_per = '比率';
    $lang->trackback_division = '複数のトラックバックは改行(Enter)で区切ってください。';

    $lang->about_supporter = '支持者とはコメント、ゲストブック、トラックバックなどを登録したユーザーを意味します。';
    $lang->supporter_rank = '支持者ランキング';
    $lang->rank = 'ランキング';
    $lang->user = 'ユーザー';
    $lang->guestbook = 'ゲストブック';
	$lang->add_denylist = 'ブロックリストに追加';
    $lang->summary = '合計';
    $lang->no_supporter = '支持者がいません。';
    $lang->about_popular = '人気コンテンツとは閲覧、コメント、ピングバック、トラックバックが多い記事を意味します。';
    $lang->popular_rank = '人気コンテンツランキング';
    $lang->read = '閲覧';
    $lang->no_popular = '人気コンテンツがありません。';
    $lang->resize_vertical = '入力スペースのサイズ調整';

    $lang->textyle_first_menus = array(
        array('dispTextyleToolDashboard','お知らせボード'),
        array('','記事管理'),
        array('','コミュニケーション管理'),
        array('','統計'),
        array('','デザイン'),
        array('','設定'),
    );

    $lang->textyle_second_menus = array(
        array(),
        array(
            'dispTextyleToolPostManageWrite'=>'新規作成',
            'dispTextyleToolPostManageList'=>'記事リスト',
            'dispTextyleToolPostManageDeposit'=>'文材保存箱',
            'dispTextyleToolPostManageCategory'=>'カテゴリー',
            'dispTextyleToolPostManageTag'=>'タグ管理',
            'dispTextyleToolPostManageBasket'=>'ごみ箱',
        ),
        array(
            'dispTextyleToolCommunicationComment'=>'コメント',
            'dispTextyleToolCommunicationGuestbook'=>'ゲストブック',
            'dispTextyleToolCommunicationTrackback'=>'トラックバック',
            'dispTextyleToolCommunicationSpam'=>'スパムブロック',
        ),
        array(
            'dispTextyleToolStatisticsVisitor'=>'訪問ユーザー',
            'dispTextyleToolStatisticsVisitRoute'=>'リファラー',
            'dispTextyleToolStatisticsSupporter'=>'支持者',
            'dispTextyleToolStatisticsPopular'=>'人気コンテンツ',
        ),
        array(
            'dispTextyleToolLayoutConfigSkin'=>'スキン選択',
            'dispTextyleToolLayoutConfigEdit'=>'HTML&middot;CSS編集',
        ),
        array(
            'dispTextyleToolConfigProfile'=>'マイプロフィル',
            'dispTextyleToolConfigInfo'=>'ブログ設定',
            'dispTextyleToolConfigPostwrite'=>'書き込みの環境設定',
            'dispTextyleToolConfigCommunication'=>'発行&middot;コミュニケーション設定',
            'dispTextyleToolConfigBlogApi'=>'BlogAPI発行',
            'dispTextyleToolConfigAddon'=>'アドオン管理',
            'dispTextyleToolConfigData'=>'データ管理',
            'dispTextyleToolConfigChangePassword'=>'パスワード変更',
        ),
    );

    $lang->cmd_go_help = 'ヘルプ';
    $lang->cmd_textyle_setup = '基本設定';
    $lang->cmd_textyle_list = 'Textyleリスト';
    $lang->cmd_textyle_creation = 'Textyle作成';
    $lang->cmd_textyle_update = 'Textyle修正';
    $lang->cmd_new_post = '新規書き込み';
    $lang->cmd_go_blog = 'ブログを見る';
    $lang->cmd_send_suggestion = 'ご意見を送る';
    $lang->cmd_view_help = 'ヘルプを見る';
    $lang->cmd_folding_menu = 'メニュー折りたたむ/展開する';
    $lang->cmd_folding_xe_news = 'お知らせを開く/閉じる';
    $lang->cmd_apply = '適用する';
    $lang->cmd_delete_favicon = 'ファビコン削除';
    $lang->cmd_change_password = 'パスワード更新';
    $lang->cmd_deny = 'ブロック';
    $lang->cmd_management = '管理';
    $lang->cmd_reply_comment = 'コメントを書く';
    $lang->cmd_change_secret = '非公開にする';
    $lang->cmd_write_relation = '関連記事を書く';
    $lang->cmd_delete_materials = '文材削除';
    $lang->cmd_restore = '差し戻し';
    $lang->cmd_empty = '空にする';
    $lang->cmd_empty_basket = 'ごみ箱を空にする';
    $lang->cmd_change_category = 'カテゴリー変更';
    $lang->cmd_publish = '発行する';
    $lang->cmd_save_temp = '臨時保存';
    $lang->cmd_edit_htmlcss = 'HTML&middot;CSS編集';
    $lang->cmd_edit_html = 'HTML編集';
    $lang->cmd_edit_css = 'CSS編集';
    $lang->cmd_use_ing = '利用中';
    $lang->cmd_new_window = '別ウィンドウ';
    $lang->cmd_select_skin = 'スキン適用';
    $lang->msg_select_skin = '選択したスキンにTextyleスキンを変更します。\n\n現在使っているスキン情報が無くなります。\n\n変更して宜しいですか？ ';
    $lang->cmd_preview_skin = 'プレビュー';
    $lang->cmd_generate_widget_code = 'ウィジェットコード生成';

    $lang->msg_already_used_url = '既に利用されているURLです。';
    $lang->alert_reset_skin = '初期化すると入力したHTML&middot;CSSの内容全てが無くなります。\n\n初期化して宜しいですか？';

	$lang->msg_close_before_write = "変更した内容が保存されてません。";

    $lang->no_post = '作成した記事がありません。<a href="%s">新しい記事を投稿して下さい。</a>';
    $lang->no_trash = 'リストから削除したものがありません。';
    $lang->no_comment = '登録されたコメントがありません。';
    $lang->no_guestbook = 'ゲストブックに登録された書き込みがありません。';
    $lang->no_trackback = '登録されたトラックバックがありません。';

    // service
    $lang->view_all = '全記事';
    $lang->search_result = '検索結果';
    $lang->category_result = 'カテゴリー';
    $lang->newest_document = '最近記事';
    $lang->newest_comment = '最近コメント';
    $lang->newest_trackback = '最近ラックバック';
    $lang->archive = '記事まとめ';
    $lang->link = 'リンクまとめ';

    $lang->mail = 'メール';
    $lang->ip = 'IPアドレス';

	$lang->secret_comment = '非公開コメント';
	$lang->insert_comment = 'コメントを書く';
	$lang->content_list = 'リスト';
	$lang->msg_input_email_address = 'メールアドレスを入力して下さい。';
	$lang->msg_input_homepage = 'ホームページを入力して下さい。';
	$lang->msg_confirm_delete_post = '臨時保存の記事は復元できません。削除して宜しいですか？';

    $lang->sample_title = 'テキスタイルへようこそ！';
    $lang->sample_tags = 'textyle ,  テキスタイル ,  テキスタイルエディター ,  文材収集機';
    $lang->msg_preparation = '準備中です。';
?>
