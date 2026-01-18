<?php
class AssetManager
{
    private $basePath;
    private $cacheDir;
    private $webCacheDir;

    public function __construct()
    {
        $this->basePath = str_replace('\\', '/', realpath(__DIR__ . '/../') . '/');
        $this->cacheDir = $this->basePath . 'assets/min/';
        $this->webCacheDir = 'assets/min/';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function getUrl($type, $files)
    {
        $hashName = md5(implode(',', $files)) . '.' . $type;
        $minifiedPath = $this->cacheDir . $hashName;
        $webPath = $this->webCacheDir . $hashName;

        $needUpdate = !file_exists($minifiedPath);
        if (!$needUpdate) {
            $cacheTime = filemtime($minifiedPath);
            foreach ($files as $file) {
                if (file_exists($this->basePath . $file) && filemtime($this->basePath . $file) > $cacheTime) {
                    $needUpdate = true;
                    break;
                }
            }
        }

        if ($needUpdate) {
            $content = '';
            foreach ($files as $file) {
                if (file_exists($this->basePath . $file)) {
                    $fileContent = file_get_contents($this->basePath . $file);

                    // NẾU LÀ CSS: Sửa đường dẫn URL trước khi nén
                    if ($type === 'css') {
                        $fileContent = $this->fixCssUrl($fileContent, $file);
                    }

                    $content .= $fileContent . "\n";
                }
            }

            if ($type === 'css') {
                $content = $this->minifyCSS($content);
            } else {
                $content = $this->minifyJS($content);
            }

            file_put_contents($minifiedPath, $content);
        }

        return $webPath . '?v=' . filemtime($minifiedPath);
    }

    private function minifyCSS($css)
    {
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        $css = str_replace(array("\r\n", "\r", "\n", "\t"), '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = str_replace(array(': ', ' {', '{ ', '; ', ', '), array(':', '{', '{', ';', ','), $css);
        return trim($css);
    }

    private function minifyJS($js)
    {
        // Chỉ xóa comment block, giữ nguyên code để đảm bảo an toàn
        $js = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
        return $js;
    }

    /**
     * Hàm quan trọng: Sửa đường dẫn url() trong CSS
     */
    private function fixCssUrl($css, $relativeFilePath)
    {
        // Lấy thư mục chứa file CSS gốc (vd: assets/fontawesome/css/)
        $sourceDir = dirname($relativeFilePath);

        // Regex tìm các đoạn url(...)
        return preg_replace_callback('/url\(\s*[\'"]?([^\'"\)]+)[\'"]?\s*\)/i', function ($matches) use ($sourceDir) {
            $url = $matches[1];

            // Bỏ qua nếu là link tuyệt đối, data-uri hoặc đã là root path
            if (strpos($url, '/') === 0 || strpos($url, 'data:') === 0 || strpos($url, 'http') === 0) {
                return $matches[0];
            }

            // Tính toán đường dẫn mới dựa trên thư mục gốc của website
            // Ví dụ: sourceDir là 'assets/fontawesome/css' và url là '../webfonts/xxx'
            // Kết quả cần là 'assets/fontawesome/webfonts/xxx'
            $combinedPath = $sourceDir . '/' . $url;

            // Làm sạch đường dẫn (xử lý các đoạn /../)
            $parts = explode('/', $combinedPath);
            $absolutes = [];
            foreach ($parts as $part) {
                if ('.' == $part)
                    continue;
                if ('..' == $part) {
                    array_pop($absolutes);
                } else {
                    $absolutes[] = $part;
                }
            }
            $finalPath = implode('/', $absolutes);

            return "url('../../" . $finalPath . "')";
            // Dùng ../../ vì file nén nằm ở assets/min/ (lùi 2 cấp để ra root)
        }, $css);
    }
}