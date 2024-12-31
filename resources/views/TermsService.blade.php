<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - Shhask</title>
    <style>
        :root {
            --background-purple: #C4B5FF;
            --dark-gray: #1A1A1A;
            --darker-gray: #242424;
            --light-gray: #333333;
            --text-color: #E0E0E0;
            --heading-color: #FFFFFF;
            --border-color: #404040;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            background: #A1A2EA;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 800px;
            margin: 2rem;
            padding: 2rem;
            background-color: var(--dark-gray);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        h1 {
            color: var(--heading-color);
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 4px solid var(--border-color);
            padding-bottom: 1rem;
        }

        h2 {
            color: var(--heading-color);
            font-size: 1.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .last-updated {
            text-align: center;
            color: #888;
            font-style: italic;
            margin-bottom: 2rem;
        }

        .section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background-color: var(--darker-gray);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .section:hover {
            transform: translateY(-2px);
            transition: transform 0.2s ease;
            background-color: var(--light-gray);
        }

        ul {
            padding-left: 1.5rem;
            color: var(--text-color);
        }

        li {
            margin-bottom: 0.5rem;
        }

        .contact-info {
            background-color: var(--darker-gray);
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
            border: 1px solid var(--border-color);
        }

        .contact-info a {
            color: var(--background-purple);
            text-decoration: none;
            font-weight: 500;
        }

        .contact-info a:hover {
            text-decoration: underline;
            opacity: 0.9;
        }

        .home-button {
            position: fixed;
            top: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            background-color: var(--dark-gray);
            border: 4px solid var(--dark-gray);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s ease;
            z-index: 1000;
        }

        .home-button:hover {
            transform: scale(1.02);
        }

        .home-button img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }

        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            .section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
<a href="/" class="home-button">
    <img src="{{asset('assets/shhask.png')}}" alt="Home">
</a>
<div class="container">
    <h1>Terms and Conditions for Shhask</h1>
    <p class="last-updated"><strong>Last updated (dd/mm/YYYY): </strong>01/01/2025</p>
    <p>Please read these Terms and Conditions carefully before using our service.</p>

    <div class="section">
        <h2>1. Interpretation and Definitions</h2>
        <h3>1.1. Interpretation</h3>
        <p>Capitalized words in these Terms and Conditions have specific meanings defined under the following conditions. These definitions apply regardless of whether they appear in singular or plural form.</p>

        <h3>1.2. Definitions</h3>
        <ul>
            <li><strong>Application</strong> refers to the Shhask mobile software provided by the Company.</li>
            <li><strong>Content</strong> means text, messages, or other materials that users create, post, or share using the Application.</li>
            <li><strong>Company</strong> (referred to as "we," "us," or "our") refers to the developers of Shhask.</li>
            <li><strong>Device</strong> means any electronic device that can access the Application, such as a smartphone or tablet.</li>
            <li><strong>Service</strong> refers to the functionality of the Shhask Application.</li>
            <li><strong>You</strong> refers to the individual accessing or using the Application.</li>
        </ul>
    </div>

    <div class="section">
        <h2>2. Acknowledgment</h2>
        <p>These Terms govern your use of Shhask and establish a binding agreement between you and the Company. By accessing or using the Application, you agree to comply with these Terms.</p>
        <p>If you disagree with any part of these Terms, you may not access or use the Application.</p>
        <p>The Application is intended for users aged 13 and older. By using the Application, you confirm that you meet this age requirement.</p>
        <p>The Company is based at 120 252 Guernica, Buenos Aires, Argentina.</p>
    </div>

    <div class="section">
        <h2>3. User Obligations</h2>
        <p>By using Shhask, you agree to:</p>
        <ul>
            <li>Not use the Application for any unlawful or malicious activities, including harassment, fraud, or spreading harmful content.</li>
            <li>Refrain from attempting to hack, decompile, or reverse-engineer any part of the Application.</li>
            <li>Avoid impersonating other individuals or entities.</li>
            <li>Report any misuse or security issues promptly to the Company.</li>
        </ul>
        <p>The Company reserves the right to terminate accounts that violate these obligations.</p>
    </div>

    <div class="section">
        <h2>4. User Accounts</h2>
        <p>To use Shhask, you must create an account by providing accurate, complete, and current information. Failure to do so may result in account suspension or termination.</p>
        <p>You are responsible for safeguarding your account credentials and notifying us immediately of unauthorized access.</p>
    </div>

    <div class="section">
        <h2>5. User-Generated Content</h2>
        <h3>5.1. Your Rights and License</h3>
        <p>You retain ownership of the content you create and share on Shhask. However, by posting or creating content in the Application, you grant the Company a non-exclusive, worldwide, perpetual, royalty-free license to use, reproduce, modify, distribute, display, and perform your content for purposes including:</p>
        <ul>
            <li>Operating and improving the Application.</li>
            <li>Marketing and promotional activities.</li>
            <li>Analytics and research.</li>
        </ul>
        <p>This license allows us to showcase your content on platforms like social media, but only in ways that respect your privacy and the purpose of the Application.</p>

        <h3>5.2. Moderation and Responsibilities</h3>
        <p>You are responsible for ensuring that your content complies with applicable laws and does not infringe upon the rights of others.</p>
        <p>The Company reserves the right to remove or restrict access to content that:</p>
        <ul>
            <li>Promotes illegal or harmful activities.</li>
            <li>Contains spam or unauthorized advertising.</li>
            <li>Violates intellectual property or privacy rights.</li>
        </ul>
        <p>Users can delete posts and block IPs to manage content visibility.</p>
    </div>

    <div class="section">
        <h2>6. Premium Features and In-App Purchases</h2>
        <p>The Application may offer premium features or cosmetic items through in-app purchases. All purchases are subject to the terms and conditions of the App Store from which you downloaded Shhask.</p>

        <h3>6.1. Refund Policy</h3>
        <p>Refunds for in-app purchases are only available as required by applicable law or through the policies of the App Store. Users experiencing issues with purchased items should contact the App Store directly.</p>
    </div>

    <div class="section">
        <h2>7. Content from Third Parties</h2>
        <p>The Company is not responsible for content submitted by third parties or users. You acknowledge that you may encounter content that is offensive, indecent, or objectionable while using the Application.</p>
        <p>We reserve the right to moderate or remove such content but are not liable for any damages resulting from exposure to it.</p>
    </div>

    <div class="section">
        <h2>8. Data Usage and Local Storage</h2>
        <p>The Application may collect basic technical data, such as IP addresses, for security and functionality purposes. Additionally, we use local storage on your device to enhance your experience, such as saving settings or session information.</p>
        <p>The Application does not track your activity beyond the intended functionality.</p>
    </div>

    <div class="section">
        <h2>9. Account Cancellation and Deletion</h2>
        <p>You may request to delete your account at any time through the Application’s settings. Upon deletion, your personal data and associated content will be permanently removed from our systems, except where retention is required for legal or compliance purposes.</p>
    </div>

    <div class="section">
        <h2>10. Intellectual Property</h2>
        <p>All intellectual property rights related to the Application, including software, branding, and design, are owned by the Company. Unauthorized use of these materials is prohibited.</p>
        <p>By granting us a license to your content, you acknowledge that the Company may use it in compliance with these Terms without requiring further consent.</p>
    </div>

    <div class="section">
        <h2>11. Compatibility and Updates</h2>
        <p>The Application may not function on certain older devices or operating systems. Periodic updates are necessary to maintain functionality and security. You are responsible for ensuring your Device meets the Application’s requirements.</p>
    </div>

    <div class="section">
        <h2>12. Governing Law and Dispute Resolution</h2>
        <p>These Terms shall be governed by the laws of Argentina. Any disputes arising from the use of Shhask will be resolved through the following process:</p>
        <p><strong>Informal Resolution:</strong> Contact us at support@shhask.com to attempt resolving disputes amicably.</p>
        <p><strong>Arbitration:</strong> If no resolution is reached, disputes will be settled through arbitration in accordance with Argentine law.</p>
    </div>

    <div class="section">
        <h2>13. Changes to These Terms</h2>
        <p>We reserve the right to modify or update these Terms at any time. Material changes will be communicated at least 30 days before becoming effective.</p>
    </div>

    <div class="contact-info">
        <h2>14. Contact Us</h2>
        <p>If you have questions about these Terms, please contact us at:</p>
        <p>Email: <a href="mailto:support@shhask.com">support@shhask.com</a></p>
        <p>Address: 120 252 Guernica, Buenos Aires, Argentina</p>
    </div>
</div>
</body>
</html>
