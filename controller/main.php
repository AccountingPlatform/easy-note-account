<?php
class main extends spController
{
	function index(){
        $this->logout = $this->spArgs('act');
        if($_SESSION['userInfo']['id']){
            $this->display('main.html');
            //$this->jump(spUrl('main','itemAdd'));
            return;
        } else {
            $this->display('main.html');
        }
	}

    function dl(){
        $receiver = 'http://open.denglu.cc/receiver';
        if ($receiver) header('location: ' . $receiver . '?' . $_SERVER['QUERY_STRING']);
    }

    function token(){
        $muid = $this->spArgs('mediaUserID');
        //$token = $this->spArgs('token');
        if($muid && is_numeric($muid)){
            $userMod = spClass('libUser');
            if($userInfo = $userMod->find(array('media_user_id'=>$muid))){
                $_SESSION['userInfo'] = $userInfo;//登录
                $this->jump(spUrl('main','itemAdd'));
                return;
            } else {
                $userInfo['nickname'] = '';
                $userInfo['media_user_id'] = $muid;
                $userInfo['token'] = '';
                $userInfo['id'] = $userMod->create($userInfo);
                $_SESSION['userInfo'] = $userInfo;//登录
                $this->jump(spUrl('main','itemAdd'));
                return;
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
        $itemInfo = array(
            'uid' => $_SESSION['userInfo']['id'],
            'title' => $this->spArgs('title','唉，我又花钱了'),
            'cate_id' => (int)$this->spArgs('cate_id',1),
            'type' => (int)$this->spArgs('type',1),
            'money' => $this->spArgs('money',0),
            'time' => time()
        );
        $itemID = (int)$this->spArgs('item_id',0);
        $itemMod = spClass('libItems');
        if($itemID){
            $itemInfo['id'] = $itemID;
            $itemMod->update(array('id'=>$itemID),$itemInfo);
            $this->success('更新成功', spUrl('main','itemList'));
        } else {
            $itemMod->create($itemInfo);
            $this->success('添加成功', spUrl('main','itemList'));
        }
        return;
    }

    function itemDel(){

    }

    function itemList(){
        if(!$_SESSION['userInfo']['id'])$this->error('请先登录',spUrl('main','index'));
        $conditions = array();
        $conditions['uid'] = $_SESSION['userInfo']['id'];
        $itemMod = spClass('libItems');
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
        $this->display('itemList.html');
    }

    function local(){
        $id = $this->spArgs('a1');
        $token = $this->spArgs('a2');
        $userMod = spClass('libUser');
        $userInfo = $userMod->find(array('id'=>$id));
        if(md5($userInfo['media_user_id']) == $token ){
            $_SESSION['userInfo'] = $userInfo;
            echo 1;
        } else {
            echo 0;
        }
    }

    function logout(){
        unset($_SESSION['userInfo']);
        $this->jump(spUrl('main','index',array('act'=>'logout')));
    }
}