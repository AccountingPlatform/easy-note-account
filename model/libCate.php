<?php
class libCate extends spModel
{
    var $pk = "id"; // 数据表的主键
    var $table = "cate"; // 数据表的名称
    var $cateList = array(
            0 => '其他',
            1 => '三餐',
            2 => '通讯费',
            3 => '穿着打扮',
            4 => '应酬',
            5 => '学习资料',
            6 => '电子产品',
            7 => '交通费用'
        );
}