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
        $conditions .= $this->conditionCreator($timeType, $time1, $time2);
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
        $this->time_type = $timeType;
        $this->display('itemList.html');
    }

    private function conditionCreator($timeType='' , $t1='', $t2=''){
        if(!$timeType) return '';
        $now = time();//现在的时间戳
        $today = date('Y-m-d',$now);//今天的日期
        $todayTimestamp = strtotime($today);//今天0点的时间戳
        $t = explode('-', $today);
        $year = $t[0];
        $month = $t[1];
        $dayNum = $t[2];
        unset($t);
        switch ($timeType) {
            case 'today':
                $time1 = $todayTimestamp;//今天0点的时间戳
                $time2 = strtotime('+1 day', $time1);//明天0点的时间戳
                break;
            case 'yesterday':
                $time1 = strtotime('-1 day', $todayTimestamp);//昨天0点的时间戳
                $time2 = $todayTimestamp;//今天0点的时间戳
                break;
            case 'thisweek':
                $temp = strtotime('last Monday',$todayTimestamp);
                //判断今天是不是周一
                if($now - $temp > 7*24*3600){
                    //今天是周一
                    $time1 = $todayTimestamp;//这周一0点的时间戳
                } else {
                    //今天不是周一
                    $time1 = $temp;//这周一0点的时间戳
                }
                $time2 = $time1 + 7*24*3600;//下周一0点的时间戳
                break;
            case 'lastweek':
                $temp = strtotime('last Monday',$todayTimestamp);
                //判断今天是不是周一
                if($now - $temp > 7*24*3600){
                    //今天是周一
                    $time1 = $temp;//上周一0点的时间戳
                } else {
                    //今天不是周一
                    $time1 = $temp - 7*24*3600;//上周一0点的时间戳
                }
                $time2 = $time1 + 7*24*3600;//这周一0点的时间戳
                break;
            case 'thismonth':
                $monthDays = days_in_month($month, $year);
                $time1 = strtotime($year.'-'.$month.'-1');
                $time2 = $time1 + $monthDays*24*3600;
                break;
            case 'lastmonth':
                if($month == 1){
                    $month = 12;
                    $year = $year - 1;
                } else {
                    $month = $month - 1;
                }
                $monthDays = days_in_month($month, $year);
                $time1 = strtotime($year.'-'.$month.'-1');
                $time2 = $time1 + $monthDays*24*3600;
                break;
            case 'custom':
                if(!$t1 || !$t2)return '';
                $time1 = strtotime($t1);
                $time2 = strtotime($t2);
                break;
            case 'all':
                return '';
                break;
        }
        return " and `time` >= '{$time1}' and `time` < '{$time2}' ";
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