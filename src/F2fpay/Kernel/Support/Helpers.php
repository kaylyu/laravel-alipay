<?php

namespace Kaylyu\Alipay\F2fpay\Kernel\Support;

/**
 * Created by PhpStorm.
 * User: kaylv <kaylv@dayuw.com>
 * Date: 2019/10/16
 * Time: 10:09
 */

/** *利用google api生成二维码图片
 * $content：二维码内容参数
 * $size：生成二维码的尺寸，宽度和高度的值
 * $lev：可选参数，纠错等级
 * $margin：生成的二维码离边框的距离
 */
function create_erweima($content, $size = '200', $lev = 'L', $margin= '0') {
    $content = urlencode($content);
    $image = '<img src="http://chart.apis.google.com/chart?chs='.$size.'x'.$size.'&amp;cht=qr&chld='.$lev.'|'.$margin.'&amp;chl='.$content.'"  widht="'.$size.'" height="'.$size.'" />';
    return $image;
}