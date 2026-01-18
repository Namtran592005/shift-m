<?php
class HtmlMinifier
{
    public static function process($buffer)
    {
        // Nếu buffer rỗng thì trả về ngay
        if (trim($buffer) === "")
            return $buffer;

        $search = array(
            '/\>[^\S ]+/s',     // Xóa khoảng trắng sau thẻ đóng
            '/[^\S ]+\</s',     // Xóa khoảng trắng trước thẻ mở
            '/(\s)+/s',         // Gộp nhiều khoảng trắng thành 1
            '/<!--(.|\s)*?-->/' // Xóa comment HTML
        );

        $replace = array(
            '>',
            '<',
            '\\1',
            ''
        );

        $buffer = preg_replace($search, $replace, $buffer);
        return $buffer;
    }
}
?>