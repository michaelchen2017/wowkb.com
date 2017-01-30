embedpano({
    swf:_config.app_url + "statics/krpano/tour.swf", 
    xml:_config.xml, 
    target:"pano-view", 
    html5:"prefer",
    wmode:_wmode(),
    mobilescale:_mobilescale()
    // onready:initPanoUi
});
$(function(){
    _krpano = new krpanoplugin(document.getElementById("krpanoSWFObject"));
    _krpano.hotspots = {};
    _krpano.comments = {};
    _krpano.scene_id = 0;
    _krpano.mobile = _krpano.get('device.mobile');
})
var comment = {}; // 说一说

function _wmode () {
    return $('html.ie').length > 0 ? 'opaque' : 'direct';
}

function _mobilescale () {
    var isWebGL = (function(el) {
        try {
            return !!(window.WebGLRenderingContext && (el.getContext("webgl") || el.getContext("experimental-webgl")));
        } catch(err) {
            return false;
        }
    })(document.createElement("canvas"));

    var u = navigator.userAgent;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1;
    var isUCBrowser = u.indexOf('UCBrowser') > -1;
    return (isAndroid && !isWebGL) || (isAndroid && isUCBrowser) ? '0.3' : '0.6';
}


//创建指定场景下的热点和评论
function addSceneHotspot(scene_name,refresh){
    _krpano.scene_id = parseInt(scene_name.replace('scene_pano_',''));
    var cache_hotspots = _krpano.hotspots[_krpano.scene_id];

    if(!(typeof(cache_hotspots) == "undefined") && !refresh){
        $.each(cache_hotspots, function (i,o) {
            _krpano.addviewhotspot(o);
        });
        $.each(_krpano.comments[_krpano.scene_id], function (i,o) {
            _krpano.addviewcomment(o);
        });
    }else{
        $.post('/?api-hotspot.htm', {scene_id: parseInt(_krpano.scene_id)}, function (result) {
            _krpano.hotspots[_krpano.scene_id] = {};
            _krpano.comments[_krpano.scene_id] = {};
            if (result.status === 1) {
                if(result.data.comment_list){
                    $.each(result.data.comment_list, function (i,o) {
                        _krpano.addviewcomment(o);
                        _krpano.comments[_krpano.scene_id][o.comment_id] = o;
                    });
                }
                if (result.data.hot_list) {
                    $.each(result.data.hot_list, function (i,o) {
                        _krpano.addviewhotspot(o);
                        _krpano.hotspots[_krpano.scene_id][o.hot_id] = o;
                    });
                }
            }
        });
    }
}

/**
* 初始化界面
*/
function initPanoUi (argument) {
    //全局UI显示
    $('.pano-ui').show();


    //作品二维码
    _krpano.set("layer[skin_qrcode].url",_config.qrcode);

    //背景音乐
    var bg_music_src = _krpano.get('config.bg_music_src');
    if(_krpano.mobile){
        if(parseInt(_krpano.get('config.mobile_bg_music')) == 1 && bg_music_src.length > 0){
            _krpano.call("playsound(bgmusic, '"+bg_music_src+"', 0)");
        }
    } else{
        if(parseInt(_krpano.get('config.pc_bg_music')) == 1 && bg_music_src.length > 0){
            _krpano.call("playsound(bgmusic, '"+bg_music_src+"', 0)");
        }
    }  
    

    //场景自动旋转
    if(parseInt(_krpano.get('config.auto_rotate')) == 1){
        _krpano.set("autorotate.enabled","true");
        _krpano.set("autorotate.speed",_krpano.get('config.auto_rotate_speed'));
    }

    //处理点赞
    if(parseInt(_config.is_like) == 1){
        _krpano.set("layer[like_btn].crop",_krpano.get("theme_config.like_crop_curr"));
    }
    _krpano.set("layer[like_txt].html",_config.like_nums);
    _krpano.set("layer[skin_hits].html","人气："+_config.hits);

}

/**
* 隐藏 html UI
*/
function hidePanoUi () {
    $('.pano-ui').hide();
}

/**
* 显示 html UI
*/
function showPanoUi () {
    $('.pano-ui').show();
}

/**
* 分享
*/
function sharePano (argument) {
    var html = $('#share-hide').html();
    openModal('html',html,'分享作品','small');
}
/**
* 点赞
*/
function likePano () {
    //是否点过
    if(_config.is_like == '0'){
        $.ajax({
            type:'GET',
            url:'/?api-like.htm&pano_id='+_config.pano_id,         
            dataType:'json',
            success:function(data){
                _config.is_like = '1';
                _krpano.set("layer[like_txt].html", data.data); 
                _krpano.set("layer[like_btn].crop",_krpano.get("theme_config.like_crop_curr"));
            }
        })
    }else{
        openModal('msg','已经赞过了哦~');
    }
}

/**
* 隐藏说一说
*/
function hideComment () {
	
    if(parseInt(_krpano.get("layer[clearscreen_con].data-hidden")) == 0){
        var cache_comments = _krpano.comments[_krpano.scene_id];
        if(!(typeof(cache_comments) == "undefined")){
            $.each(cache_comments, function (i,o) {
                _krpano.call('removehotspot(comment_'+o.comment_id+')');
            });
        } 
		console.log(_krpano.get("theme_config.clearscreen_crop_curr"))
        _krpano.set("layer[clearscreen_btn].crop",_krpano.get("theme_config.clearscreen_crop_curr"));  
        _krpano.set("layer[clearscreen_con].data-hidden", "1"); 
    }else{
		console.log(_krpano.get("theme_config.clearscreen_crop_def"))
        addSceneHotspot('scene_pano_' + _krpano.scene_id);
        _krpano.set("layer[clearscreen_btn].crop",_krpano.get("theme_config.clearscreen_crop_def")); 
        _krpano.set("layer[clearscreen_con].data-hidden", "0"); 
    }
    
}

/**
* 点击说一说 PC
*/
function commentFormPano () {

    if(_user.user_id){
        _krpano.addcommentavatar();
        $('.comment-wrap').show();
        _krpano.call('skin_hide_ui');
        _krpano.set('hotspot[comment_public].visible','true');
        _krpano.set('hotspot[comment_avatar_public].visible','true');
        _krpano.set('hotspot[comment_text_public].visible','true');
        $('#comment-form #content').focus();
    }else{
        $('#login-modal').modal({backdrop:'static'});
    }
}

/**
* 点击说一说 mobile
*/
function mCommentFormPano () {
    if(_config.is_weixin){
        _krpano.addcommentavatar();
        $('.comment-wrap').show();
        _krpano.call('skin_hide_ui');
        _krpano.set('hotspot[comment_public].visible','true');
        _krpano.set('hotspot[comment_avatar_public].visible','true');
        _krpano.set('hotspot[comment_text_public].visible','true');
    }else{
        openModal('msg','请使用微信访问再使用说一说哦');
    }
}


/**
* 隐藏说一说拖动头像及表单
*/
function hideCommentFormPano () {
    _krpano.set('hotspot[comment_public].visible','false');
    _krpano.set('hotspot[comment_avatar_public].visible','false');
    _krpano.set('hotspot[comment_text_public].visible','false');
    $('.comment-wrap').hide();
    _krpano.call('skin_show_ui');
}



/**
* 弹出层
*/
function modalPano (type,type_id,title,data) {

    switch(type){
        case 'menu' : //菜单
            switch(type_id){
                case '1' : //链接
                    _krpano.call( "openurl(" + data + ",'_blank');" );
                break;
                
                case '2' : //图文
                    $.ajax({
                        url: "/?api-article_detail.htm&article_id=" + data,
                        success: function(data){
                            if(data.status != 1){
                                openModal('msg',data.msg);
                            }else{
                                var btn = '';
                                if(data.data.btns && typeof(data.data.btns.btn_show) != 'undefined' && data.data.btns.btn_show == '1'){
                                    btn = '<a class="button" target="_blank" href="'+data.data.btns.btn_url+'">'+data.data.btns.btn_title+'</a>';
                                }
                                openModal('html',data.data.content + btn,data.data.title);
                            }
                        }
                    });
                break;
                
                case '3' : //地图导航
                    var data = data.split('|');
                    var url = "http://3gimg.qq.com/lightmap/v1/marker/?marker=coord:"+data[0]+","+data[1]+";title:"+data[2]+";addr:"+data[2]+";&referer=myapp;&key=NFXBZ-JUOCS-HZ4OP-6XOI3-J4EEE-NABW6";
                    if(_config.client == 'mobile' || _config.client == 'weixin'){
						location.href = url;
					}else{
						return openModal('iframe',url,title,'full');
					}
					
                break;

                case '4' : //电话拨号
                    if(_config.client == 'mobile' || _config.client == 'weixin'){
                        _krpano.call( "openurl(tel:" + data + ",'_blank');" );
                    }else{
                        openModal('html','<p style="text-align:center;font-size:20px;">'+data+'</p>',title,'small');
                    }
                break;
           }

        break;

        case 'remark' : //作品简介
                data = '<div style="min-height:400px">'+_config.remark+'</div>';
            return openModal('html',data,'作品简介');
        case 'scene_remark' : //场景简介
            var data = _krpano.get('scene[get(currscene)].remark');
                data = '<div style="min-height:400px">'+data+'</div>';
            return openModal('html',data,'场景介绍');
        break;
    }
    
}

//modal
function openModal(type,data,title,size){

    switch(type){
        case 'iframe' :
            $('body').createModal({iframe: data,title:title,size:size});
        break;
        case 'html' :
            $('body').createModal({html: data,title:title,size:size});
        break;
        case 'msg' :
            $('body').createModal({msg: data,bgClose:false});
        break;
    }
}


/**
* 添加热点
*/
krpanoplugin.prototype.addviewhotspot = function(hotspotdata){
    var hotspotName = "hotspot_" + hotspotdata.hot_id;
    var textName = "hotspot_txt_" + hotspotdata.hot_id;
    // var ondown = "spheretoscreen(ath, atv, hotspotcenterx, hotspotcentery); sub(drag_adjustx, mouse.stagex, hotspotcenterx); sub(drag_adjusty, mouse.stagey, hotspotcentery); asyncloop(pressed, sub(dx, mouse.stagex, drag_adjustx); sub(dy, mouse.stagey, drag_adjusty); screentosphere(dx, dy, ath, atv);); ";
    var ondown = "";
    var onclick = "js(_krpano.clcikhotspot(" + hotspotdata.hot_id + "));";
    var onover = "set(layer["+textName+"].alpha,0.8)";
    var onout = "set(layer["+textName+"].alpha,1)";

    //热点
    var hotspot = this.addhotspot(hotspotName, {
        id: hotspotdata.hot_id,
        type: "image",
        url: hotspotdata.url,
        keep: "false",
        enabled: "true",
        scale: hotspotdata.scale,
        ath: hotspotdata.ath,
        atv: hotspotdata.atv,
        v: hotspotdata.v,
        edge: "bottom",
        bgcapture: true,
        ishotspot: true,
        handcursor: true,
        ondown: ondown,
        onclick: onclick,
        onover: onover,
        onout: onout
    });

    //热点名称
    if (hotspotdata.hot_name) {
        hotspotManageBgY = -20 + parseInt(hotspotdata.y) - 2;
        this.addlayer(textName, {
            parent: hotspot,
            url: "%SWFPATH%/plugins/textfield.swf",
            html: hotspotdata.hot_name,
            alpha: "1",
            enabled: "true",
            align: "top",
            edge: "bottom",
            autowidth: "true",
            y: hotspotdata.y,
            backgroundcolor: "0x000000",
            backgroundalpha: "0.4",
            padding: "5 8",
            roundedge: '4',
            css: " font-size: 14px;color: #FFFFFF;font-family:黑体;",
            zorder: 1,
            keep: false,
            handcursor: true,
            onclick: onclick
        });
    }
    _krpano.call( "hotspot[" + hotspotName + "].loadstyle(" + hotspotdata.style + ")" );

    //vr 热点
    if(hotspotdata.type_id == '1'){
        if (hotspotdata.hot_name) {
            this.addhotspot("vr_hotspot_text_" + hotspotdata.hot_id, {
                id: hotspotdata.hot_id,
                url: "/?api-text_img.htm&text=" + hotspotdata.hot_name,
                width: "120",
                height: "18",
                ox: "0",
                oy: "-90",
                ath: hotspotdata.ath, 
                atv: hotspotdata.atv,
                handcursor: true,
                keep: false,
                distorted:true,
                depth:"1001",
                zorder:"4",
                isvr:true,
                visible:false,
                onclick: onclick
            });
             _krpano.call( "hotspot[vr_hotspot_text_" + hotspotdata.hot_id + "].loadstyle(hotspotvr_style)" );
        }
        this.addhotspot("vr_hotspot_img_" + hotspotdata.hot_id, {
            id: hotspotdata.hot_id,
            url: _krpano.get('scene[scene_pano_'+hotspotdata.data_id+'].thumburl'),
            width: "34",
            height: "34",
            ox: "0",
            oy: "-46",
            ath: hotspotdata.ath, 
            atv: hotspotdata.atv,
            handcursor: true,
            keep: false,
            distorted:true,
            depth:"1000",
            zorder:"1",
            isvr:true,
            visible:false,
            onclick: onclick
        });
        this.addhotspot("vr_hotspot_" + hotspotdata.hot_id, {
            id: hotspotdata.hot_id,
            type: "image",
            url: "%SWFPATH%/images/bg-vr-hotspot.png",
            keep: false,
            ath: hotspotdata.ath,
            atv: hotspotdata.atv,
            oy:"-40",
            handcursor: true,
            scale: 0.5,
            distorted:true,
            depth:"1000",
            zorder:"2",
            isvr:true,
            visible:false,
            onclick: onclick
        });
        _krpano.call( "hotspot[vr_hotspot_img_" + hotspotdata.hot_id + "].loadstyle(hotspotvr_style)" );
        _krpano.call( "hotspot[vr_hotspot_" + hotspotdata.hot_id + "].loadstyle(hotspotvr_style)" );
    }
    return this;
};

/**
* 热点点击动作
*/
krpanoplugin.prototype.clcikhotspot = function(hot_id){
    var hotspot = _krpano.hotspots[_krpano.scene_id][hot_id];
   
    switch(hotspot.type_id){
        //1 场景漫游 2 产品连接 3 超链接 4 全景跳转 5 3d环物 6 图文信息 7 无 8 多媒体
        case '1':
            _krpano.call( "skin_loadscene(scene_pano_" + hotspot.data_id + ");" );
        break;
        
        case '5':
            $.ajax({
                url: _config.app_url + "?api-rotate_info.htm&rotate_id=" + hotspot.data_id,
                success: function(data){
                    if(data.status != 1){
                        openModal('msg',data.msg);
                    }else{
                        openModal('iframe',_config.app_url + '?api-rotate_view.htm&rotate_id='+ data.data.rotate_id,data.data.title,'full');
                    }
                }
            });
        break;
        
        case '3':
            _krpano.call( "openurl(" + hotspot.v + ",'_blank');" );
        break;
        
        case '6':
            modalPano('menu','2',hotspot.hot_name,hotspot.data_id);
        break;
        case '7':
            openModal('msg','这里什么都没有 ^_^');
        break;
        case '8':
            var html = '<div class="player-audio" style="text-align:center"><audio controls="controls" autoplay="autoplay"><source type="audio/mpeg" src="'+hotspot.v+'"></source>您的浏览器不支持HTML5播放音频</audio></div>';
            openModal('html',html,hotspot.hot_name,'small');
        break;
    }
}

/**
* 语音评论点击
*/
krpanoplugin.prototype.clickvoice = function(comment_id){
    var comment = _krpano.comments[_krpano.scene_id][comment_id];
    _krpano.call("skin_play_music(comment, '"+comment.content+"', '"+comment.comment_id+"')");
}

/**
* 添加评论
*/
krpanoplugin.prototype.addviewcomment = function(hotspotdata){
    // console.log(hotspotdata);

    var hotspotName = "comment_" + hotspotdata.comment_id;
    var avatarName = "comment_avatar_" + hotspotdata.comment_id;
    var avatarParentName = "comment_avatar_parent_" + hotspotdata.comment_id;
    var textName = "comment_text_" + hotspotdata.comment_id;

    var hotspot = this.addhotspot(hotspotName, {
        id: hotspotdata.comment_id,
        type: "image",
        url: "/statics/krpano/images/bg-comment.png",
        scale: 1,
        ath: hotspotdata.ath,
        atv: hotspotdata.atv,
        crop:'0|0|30|14',
        handcursor: false,
        is_comment: true,
        enabled: hotspotdata.type_id == '10' ? 'false' : 'true'
    });

    var avatarParent = this.addlayer(avatarParentName, {
        parent: hotspot,
        type:'container',
        maskchildren: "true",
        align: "top",
        edge: "bottom",
        height:'30',
        width:'30',
        bgroundedge: '15'
    });

    this.addlayer(avatarName, {
        parent: avatarParent,
        url: hotspotdata.avatar,
        align: "bottom",
        edge: "bottom",
        height:'30',
        width:'30',
        handcursor: false
    });
    if(hotspotdata.type_id == '10'){ //文字
        
        this.addlayer(textName, {
            parent: hotspot,
            url: "%SWFPATH%/plugins/textfield.swf",
            html: hotspotdata.content,
            align: "top",
            edge: "left",
            autowidth: "true",
            x:'22',
            y:'-16',
            backgroundcolor: "0x000000",
            backgroundalpha: "0.4",
            padding: "5 8",
            roundedge: '4',
            css: " font-size: 12px;color: #FFFFFF;font-family:黑体;",
            handcursor: false
        });

    }else{
        var voiceParentName = "comment_voice_parent_" + hotspotdata.comment_id;
        var voiceName = "comment_voice_" + hotspotdata.comment_id;
        var voicePlayingName = "comment_voice_playing_" + hotspotdata.comment_id;
        var onclickVoice = "js(_krpano.clickvoice(" + hotspotdata.comment_id + "));";
        var voiceParent = this.addlayer(voiceParentName, {
            parent: hotspot,
            type:'container',
            // maskchildren: "true", //SB UC浏览器
            align: "top",
            edge: "left",
            height:'26',
            width:'32',
            x:'22',
            y:'-14',
            bgroundedge: '4',
            bgcolor:"0x000000",
            bgalpha:"0.4"
        });

        this.addlayer(voiceName, {
            parent: voiceParent,
            url: '/statics/krpano/images/bg-voice.png',
            crop:'0|52|26|26',
            x:'2',
            align: "bottom",
            edge: "bottom",
            height:'26',
            width:'26',
            enabled: "true",
            onclick:onclickVoice
        });
        this.addlayer(voicePlayingName, {
            parent: voiceParent,
            url: '/statics/krpano/images/bg-voice.png',
            x:'2',
            align: "bottom",
            edge: "bottom",
            height:'26',
            width:'26',
            enabled: "true",
            onclick:onclickVoice,
            visible:'false'
        });
        _krpano.call( "layer[comment_voice_playing_" + hotspotdata.comment_id + "].loadstyle(comment_voice_style)" );
    }
    return this;
}

/**
* 添加评论头像拖拽层
*/
krpanoplugin.prototype.addcommentavatar = function(){

    var hotspotName = "comment_public";
    var avatarName = "comment_avatar_public";
    var textName = "comment_text_public";
    var atv = comment.atv = _krpano.get('view.vlookat');
    var ath = comment.ath = _krpano.get('view.hlookat');
    var ondown = "spheretoscreen(ath, atv, hotspotcenterx, hotspotcentery); sub(drag_adjustx, mouse.stagex, hotspotcenterx); sub(drag_adjusty, mouse.stagey, hotspotcentery); asyncloop(pressed, sub(dx, mouse.stagex, drag_adjustx); sub(dy, mouse.stagey, drag_adjusty); screentosphere(dx, dy, ath, atv);set(hotspot[comment_public].atv,get(atv)); set(hotspot[comment_public].ath,get(ath));set(hotspot[comment_text_public].atv,get(atv)); set(hotspot[comment_text_public].ath,get(ath));set(hotspot[comment_avatar_public].atv,get(atv)); set(hotspot[comment_avatar_public].ath,get(ath));roundval(ath, 3);roundval(atv, 3);js(_krpano.movecommentavatar(get(atv),get(ath));););";
    

    var hotspot = this.addhotspot(hotspotName, {
        type: "image",
        url: "/statics/krpano/images/bg-comment.png",
        scale: 1,
        ath: ath,
        atv: atv,
        crop:'0|0|30|14',
        handcursor: false
    });

    this.addhotspot(avatarName, {
        type: "image",
        url: _user.avatar,
        scale: 1,
        align: "top",
        edge: "bottom",
        ath: ath,
        atv: atv,
        width:35,
        height:35,
        oy:'-7',
        handcursor: true,
        ondown:ondown
    });
        
    this.addhotspot(textName, {
        url: "%SWFPATH%/plugins/textfield.swf",
        html: '拖动头像到需要评论的地方...',
        scale: 1,
        align: "top",
        edge: "left",
        ath: ath,
        atv: atv,
        ox:'25',
        oy:'-25',
        backgroundcolor: "0x000000",
        backgroundalpha: "0.4",
        padding: "5 8",
        roundedge: '4',
        css: " font-size: 15px;color: #FF9900;font-family:黑体;",
        handcursor: true,
        ondown:ondown
    });
    return this;
}

/**
* 评论头像拖拽
*/
krpanoplugin.prototype.movecommentavatar = function (atv,ath) {
    comment.atv = atv;
    comment.ath = ath;
}



//提交PC说一说
$('#comment-form').submit(function(){
    var self = $(this);
    var submit = self.find(".btn-primary");
        submit.attr("disabled", true);
    var data = {
        user_id:_user.user_id,
        pano_id:_config.pano_id,
        scene_id:_krpano.scene_id,
        content:self.find('#content').val(),
        ath:comment.ath,
        atv:comment.atv
    };


    $.post('/?api-pc_comment.htm', data, success, "json");
    return false;
    
    function success(data) {
        if (data.status == 1) {
            hideCommentFormPano();
            data.data.avatar    = _user.avatar;
            data.data.ath       = comment.ath;
            data.data.atv       = comment.atv;
            _krpano.addviewcomment(data.data);
            _krpano.hotspots[_krpano.scene_id] = undefined;

        } else {
            openModal('msg',data.msg);
        }
        submit.attr("disabled", false);
    }
})