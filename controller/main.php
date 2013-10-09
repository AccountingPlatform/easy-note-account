<?php
class main extends spController
{
	function index(){
        $this->logout = $this->spArgs('act');
        if($_SESSION['userInfo']['id']){
            $this->jump(spUrl('main','itemAdd'));
            return;
        } else {
            $this->display('main.html');
        }
	}

    function bindOtherSocialPlatform(){
        if($_SESSION['userInfo']['id']){
            $this->display('bindOtherSocialPlatform.html');
        } else {
            $this->jump(spUrl('main','index'));
        }
        return;
    }

    function dl(){
        $receiver = 'http://open.denglu.cc/receiver';
        if ($receiver) header('location: ' . $receiver . '?' . $_SERVER['QUERY_STRING']);
    }

    function token(){
        global $apiID,$apiKey;
        $api = spClass('Denglu',array($apiID,$apiKey,'utf-8'));
        $muid = $this->spArgs('mediaUserID');
        $token = $this->spArgs('token');
        $userMod = spClass('libUser');
        $socialMod = spClass('libSocial');
        if($_SESSION['userInfo']['id']){
            //绑定其他的社交平台
            if($socialMod->find(array('media_user_id'=>$muid))){
                $this->error('该平台已经绑定过了',spUrl('main','bindOtherSocialPlatform'));
                return;
            } else {
                $socialMod->create(array('uid'=>$_SESSION['userInfo']['id'],'media_user_id'=>$muid));
                //绑定
                try{
                    $result = $api->bind( $muid, $_SESSION['userInfo']['id']);
                }catch(DengluException $e){
                    //return false;     
                    echo $e->geterrorCode();  //返回错误编号
                    echo $e->geterrorDescription();  //返回错误信息
                }

                $pic = 'http://jizhang.ohshit.cc/public/img/money.jpg';
                $api->share( $muid, '我刚刚登录了{记账}应用，一款简洁到不能再简洁的应用。【记账】http://jizhang.ohshit.cc', 'http://jizhang.ohshit.cc', $_SESSION['userInfo']['id']);
                $this->success('绑定成功', spUrl('main','index'));
            }
        } else {
            //正常登录
            if(!empty($token)){
                try{
                    $info = $api->getUserInfoByToken($token);
                }catch(DengluException $e){//获取异常后的处理办法(请自定义)
                    //return false;     
                    echo $e->geterrorCode();  //返回错误编号
                    echo $e->geterrorDescription();  //返回错误信息
                }
            }
            if($muid && is_numeric($muid)){
                if($socialInfo = $socialMod->find(array('media_user_id'=>$muid))){
                    $userInfo = $userMod->find(array('id'=>$socialInfo['uid']));
                    //临时的，等uid为2的用户的nickname更新后，删掉该代码
                    if($userInfo['nickname']==''){
                        $userMod->updateField(array('id'=>$socialInfo['uid']),'nickname',$info['screenName']);
                    }
                    //临时的绑定
                    try{
                        $result = $api->getBind( '', $userInfo['id']);
                    }catch(DengluException $e){
                        //return false;     
                        echo $e->geterrorCode();  //返回错误编号
                        echo $e->geterrorDescription();  //返回错误信息
                    }
                    if(!$result){
                        try{
                            $api->bind( $muid, $userInfo['id']);
                        }catch(DengluException $e){
                            //return false;     
                            echo $e->geterrorCode();  //返回错误编号
                            echo $e->geterrorDescription();  //返回错误信息
                        }
                    }
                    //登录
                    $userInfo['social'] = $socialMod->findAll(array('uid'=>$socialInfo['uid']));
                    $_SESSION['userInfo'] = $userInfo;
                    $api->sendLoginFeed($muid);
                    $this->jump(spUrl('main','itemAdd'));
                    return;
                } else {
                    $userInfo['nickname'] = $info['screenName'];
                    $userInfo['email'] = '';
                    //增加新用户
                    $userInfo['id'] = $userMod->create($userInfo);

                    $socialInfo['uid'] = $userInfo['id'];
                    $socialInfo['media_user_id'] = $muid;
                    //增加绑定关系
                    $socialMod->create($socialInfo);
                    
                    //绑定
                    try{
                        $result = $api->bind( $muid, $userInfo['id']);
                    }catch(DengluException $e){
                        //return false;     
                        echo $e->geterrorCode();  //返回错误编号
                        echo $e->geterrorDescription();  //返回错误信息
                    }
                    //登录
                    $userInfo['social'] = $socialMod->findAll(array('uid'=>$userInfo['id']));
                    $_SESSION['userInfo'] = $userInfo;
                    $api->sendLoginFeed($muid);
                    $this->jump(spUrl('main','itemAdd'));
                    return;
                }
            }
        }
        
    }

    function bind(){
        echo 'bind';
    }

    function itemAdd(){
        if(!$_SESSION['userInfo']['id'])$this->error('请先登录',spUrl('main','index'));
        $itemID = (int)$this->spArgs('id');
        if($itemID){
            $itemMod = spClass('libItems');
            $itemInfo = $itemMod->find(array('id'=>$itemID,'uid'=>$_SESSION['userInfo']['id']));
            if(!$itemInfo){
                $this->error('no item',spUrl('main','itemList'));
                return;
            }
            $this->itemID = $itemID;
            $this->itemInfo = $itemInfo;
        }

        $cateMod = spClass('libCate');
        $this->cateList = $cateMod->cateList;

        $this->a1 = $_SESSION['userInfo']['id'];
        $this->a2 = md5($_SESSION['userInfo']['media_user_id']);
        $this->display('itemAdd.html');
    }

    function itemAddSave(){
        if(!$_SESSION['userInfo']['id'])$this->error('请先登录',spUrl('main','index'));
        global $apiID,$apiKey;
        $api = spClass('Denglu',array($apiID,$apiKey,'utf-8'));
        $itemInfo = array(
            'uid' => $_SESSION['userInfo']['id'],
            'title' => $this->spArgs('title','唉，我又花钱了'),
            'cate_id' => (int)$this->spArgs('cate_id',1),
            'type' => (int)$this->spArgs('type',1),
            'money' => $this->spArgs('money',0)
        );
        $itemID = (int)$this->spArgs('item_id',0);
        $itemMod = spClass('libItems');
        if($itemID){
            $itemInfo['id'] = $itemID;
            $itemInfo['time'] = strtotime($this->spArgs('time'));
            $itemMod->update(array('id'=>$itemID),$itemInfo);
            $this->success('更新成功', spUrl('main','itemList'));
        } else {
            $itemInfo['time'] = time();
            $itemMod->create($itemInfo);
            if($this->spArgs('share') == 'on'){
                //分享到各个平台
                if($itemInfo['type']==1){
                    $pic = 'http://jizhang.ohshit.cc/public/img/out.jpg';
                    $shareInfo = '我刚刚花了 ' . $itemInfo['money'] . '元 ，' . $itemInfo['title'] . '。【记账】http://jizhang.ohshit.cc';
                } else {
                    $pic = 'http://jizhang.ohshit.cc/public/img/in.jpg';
                    $shareInfo = '我刚刚收入了 ' . $itemInfo['money'] . '元 ，' . $itemInfo['title'] . '。【记账】http://jizhang.ohshit.cc';
                }
                
                $socialMod = spClass('libSocial');
                $socialInfo = $socialMod->findAll(array('uid'=>$_SESSION['userInfo']['id']));
                foreach ($socialInfo as $k => $v) {
                    try{
                        $api->share( $v['media_user_id'] , $shareInfo , 'http://jizhang.ohshit.cc', $v['uid'] , $pic);
                    }catch(DengluException $e){
                        //return false;     
                        echo $e->geterrorCode();  //返回错误编号
                        echo $e->geterrorDescription();  //返回错误信息
                    }
                }
            }
            $this->success('添加成功', spUrl('main','itemList'));
        }
        return;
    }

    function itemDel(){

    }

    function itemList(){
        if(!$_SESSION['userInfo']['id'])$this->error('请先登录',spUrl('main','index'));
        $timeType = $this->spArgs('time_type','today');
        $time1 = $this->spArgs('t1');
        $time2 = $this->spArgs('t2');
        $conditions = '`uid` = ' . $_SESSION['userInfo']['id'] . ' ';
        $itemMod = spClass('libItems');
        $conditions .= $itemMod->conditionCreator($timeType, $time1, $time2);
        $this->itemLists = $itemMod->findAll($conditions);

        $outSum = 0;
        $inSum = 0;
        foreach ($this->itemLists as $k => $v) {
            if($v['type']==1){
                $outSum += $v['money'];
            } else {
                $inSum += $v['money'];
            }
        }

        $cateMod = spClass('libCate');
        $this->cateList = $cateMod->cateList;
        $this->typeList = array(1=>'支出',2=>'收入');
        $this->out_sum = $outSum;
        $this->in_sum = $inSum;
        $this->time_type = $timeType;
        $this->display('itemList.html');
    }

    function local(){
        $id = $this->spArgs('a1');
        $token = $this->spArgs('a2');
        $userMod = spClass('libUser');
        $socialMod = spClass('libSocial');
        $socialInfo = $socialMod->findAll(array('uid'=>$id));
        foreach ($socialInfo as $k => $v) {
            if(md5($v['media_user_id']) == $token ){
                $userInfo = $userMod->find(array('id'=>$id));
                $userInfo['social'] = $socialInfo;
                echo 1;
                return;
            }
        }
        echo 0;
        return;
    }

    function comment(){
        $this->display('comment.html');
    }

    function logout(){
        unset($_SESSION['userInfo']);
        $this->jump(spUrl('main','index',array('act'=>'logout')));
    }
}