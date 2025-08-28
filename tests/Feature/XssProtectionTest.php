<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class XssProtectionTest extends TestCase
{
    #[Test]
    public function malicious_script_in_login_form_is_blocked()
    {
        Livewire::test('pages.auth.login')
            ->set('form.email', '<script>alert("XSS")</script>@example.com')
            ->set('form.password', 'password123')
            ->call('login')
            ->assertHasErrors(['form.email']);

        $this->assertGuest();
    }

    #[Test]
    public function html_tags_in_input_are_escaped_in_livewire_component()
    {
        $user = User::factory()->create([
            'name' => '<script>alert("XSS")</script>John',
            'email' => 'john125@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user);

        Livewire::test('views.dashboard')
            ->assertSee(htmlspecialchars($user->name, ENT_QUOTES))
            ->assertDontSee('<script>alert("XSS")</script>');
    }

    #[Test]
    public function security_headers_are_present_in_livewire_component()
    {
        $component = Livewire::test('pages.auth.login');

        $component->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-XSS-Protection', '1; mode=block')
            ->assertHeaderMissing('Server');
    }

    #[Test]
    public function content_security_policy_is_set_in_livewire_component()
    {
        $component = Livewire::test('pages.auth.login');

        $csp = $component->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
    }

    #[Test]
    public function javascript_injection_in_form_fields_is_prevented_in_livewire_component()
    {
        $maliciousInputs = [
            'email' => 'javascript:alert("XSS")',
            'password' => '<img src=x onerror=alert("XSS")>',
            'remember' => '<script>document.cookie="stolen"</script>',
        ];

        Livewire::test('pages.auth.login')
            ->set('form.email', $maliciousInputs['email'])
            ->set('form.password', $maliciousInputs['password'])
            ->set('form.remember', $maliciousInputs['remember'])
            ->call('login')
            ->assertHasErrors(['form.email', 'form.password', 'form.remember'])
            ->assertGuest();
    }

    #[Test]
    public function reflected_xss_is_prevented_in_livewire_component()
    {
        $component = Livewire::test('pages.auth.login', ['error' => '<script>alert("XSS")</script>']);

        $component->assertDontSee('<script>alert("XSS")</script>', false)
            ->assertSee(htmlspecialchars('<script>alert("XSS")</script>', ENT_QUOTES));
    }
}
