<?php
class libItems extends spModel
{
    var $pk = "id"; // 数据表的主键
    var $table = "items"; // 数据表的名称

    //组装日期条件
    function conditionCreator($timeType='' , $t1='', $t2=''){
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
}