<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class XssProtectionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function malicious_script_in_login_form_is_blocked()
    {
        $maliciousEmail = '<script>alert("XSS")</script>@example.com';

        $response = $this->post('/login', [
            '_token' => csrf_token(),
            'email' => $maliciousEmail,
            'password' => 'password123',
        ]);

        // Should fail validation (invalid email format)
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    #[Test]
    public function html_tags_in_input_are_escaped()
    {
        $user = User::factory()->create([
            'name' => '<script>alert("XSS")</script>John',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        // HTML should be escaped, not executed
        $response->assertSee('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;John');
        $response->assertDontSee('<script>alert("XSS")</script>');
    }

    #[Test]
    public function security_headers_are_present()
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeaderMissing('Server'); // Hide server information
    }

    #[Test]
    public function content_security_policy_is_set()
    {
        $response = $this->get('/login');

        $response->assertHeader('Content-Security-Policy');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContains("default-src 'self'", $csp);
        $this->assertStringContains("script-src 'self'", $csp);
        $this->assertStringContains("object-src 'none'", $csp);
    }

    #[Test]
    public function javascript_injection_in_form_fields_is_prevented()
    {
        $maliciousInputs = [
            'email' => 'javascript:alert("XSS")',
            'password' => '<img src=x onerror=alert("XSS")>',
            'remember' => '<script>document.cookie="stolen"</script>',
        ];

        $response = $this->post('/login', array_merge($maliciousInputs, [
            '_token' => csrf_token(),
        ]));

        // Should fail validation
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    #[Test]
    public function reflected_xss_is_prevented()
    {
        // Attempt reflected XSS through URL parameters
        $response = $this->get('/login?error=<script>alert("XSS")</script>');

        // Script should be escaped in output
        $response->assertDontSee('<script>alert("XSS")</script>', false);
        $response->assertSee('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', false);
    }
}
