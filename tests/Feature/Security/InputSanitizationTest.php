<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class InputSanitizationTest extends TestCase
{
    /** ALPHA-005: comments are reduced to plain text (all HTML stripped). */
    public function test_sanitize_comment_strips_all_html(): void
    {
        $this->assertSame('hello', sanitizeComment('<img src=x onerror=alert(1)>hello'));
        $this->assertSame('bold text', sanitizeComment('<b>bold</b> text'));
        $this->assertStringNotContainsString('<script', sanitizeComment('<script>alert(1)</script>'));
    }

    /** ALPHA-015: event-handler attributes are removed from allowed tags. */
    public function test_sanitize_html_removes_event_handlers(): void
    {
        $clean = sanitizeHtml('<img src="/ok.png" onerror="alert(document.cookie)">');
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringContainsString('/ok.png', $clean);
    }

    /** ALPHA-015: javascript: URL schemes are removed from href/src. */
    public function test_sanitize_html_removes_javascript_scheme(): void
    {
        $clean = sanitizeHtml('<a href="javascript:alert(1)">x</a>');
        $this->assertStringNotContainsString('javascript:', $clean);

        $cleanImg = sanitizeHtml('<iframe src="javascript:alert(1)"></iframe>');
        $this->assertStringNotContainsString('javascript:', $cleanImg);
    }

    /** ALPHA-015: safe markup is preserved. */
    public function test_sanitize_html_keeps_safe_markup(): void
    {
        $clean = sanitizeHtml('<p>Hello <strong>world</strong> <a href="https://example.com">link</a></p>');
        $this->assertStringContainsString('<strong>world</strong>', $clean);
        $this->assertStringContainsString('https://example.com', $clean);
    }

    /** ALPHA-015: the content() helper (used for post bodies) neutralizes XSS payloads. */
    public function test_content_helper_neutralizes_xss(): void
    {
        $out = content('<img src=x onerror="alert(1)"><script>alert(2)</script><p>ok</p>');
        $this->assertStringNotContainsString('onerror', $out);
        $this->assertStringNotContainsString('<script', $out);
    }

    /** Safe inline raster images (data:image base64) survive — WYSIWYG editors embed these. */
    public function test_sanitize_html_keeps_safe_data_image(): void
    {
        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $clean = sanitizeHtml('<img src="'.$png.'">');
        $this->assertStringContainsString('data:image/png;base64,', $clean);
    }

    /** But data: SVG and other data: payloads are still stripped (SVG can carry script). */
    public function test_sanitize_html_strips_unsafe_data_uris(): void
    {
        $svg = sanitizeHtml('<img src="data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=">');
        $this->assertStringNotContainsString('data:image/svg', $svg);

        $html = sanitizeHtml('<a href="data:text/html;base64,PHNjcmlwdD4=">x</a>');
        $this->assertStringNotContainsString('data:text/html', $html);
    }
}
