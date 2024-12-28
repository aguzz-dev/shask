<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Shhask</title>
</head>
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

    /* To replace with an image later, just add this class */
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

<body>
    <a href="/" class="home-button">
        <img src="{{asset('assets/shhask.png')}}" alt="Home">
    </a>
    <div class="container">
        <h1>Privacy Policy for Shhask</h1>
        <p class="last-updated">Last updated (dd/mm/YYYY): 28/12/2024</p>

        <div class="section">
            <p>Shhask is committed to protecting the privacy of its users. This Privacy Policy explains how we collect,
                use, store, and safeguard your personal information when using our mobile application ("Shhask" or "the
                Application").</p>
        </div>

        <div class="section">
            <h2>1. Information We Collect</h2>
            <p>To provide you access to the Application and its features, we collect the following personal information
                during registration:</p>
            <ul>
                <li>Full name</li>
                <li>Username</li>
                <li>Date of birth</li>
                <li>Email address</li>
                <li>Password</li>
            </ul>
            <p>We also collect limited technical information, such as IP addresses, to support security and moderation
                features like message blocking.</p>
        </div>

        <div class="section">
            <h2>2. How We Use Your Information</h2>
            <p>The information we collect is used exclusively for the following purposes:</p>
            <ul>
                <li>To provide, manage, and personalize your experience on Shhask</li>
                <li>To enable essential application features, such as sending and receiving anonymous questions</li>
                <li>To ensure user safety through tools like IP-based message blocking</li>
                <li>To communicate with you about updates, technical support, or notifications related to your account
                </li>
            </ul>
            <p>Note: Shhask does not sell or share your personal information with third parties for commercial purposes.
            </p>
        </div>

        <div class="section">
            <h2>3. Content Moderation</h2>
            <p>Shhask allows users to:</p>
            <ul>
                <li>Block messages from specific senders via their IP</li>
                <li>Delete the public link used to receive questions at any time</li>
            </ul>
        </div>

        <div class="section">
            <h2>4. Data Storage and Processing</h2>
            <p>The information we collect is securely stored on servers located in various global regions, chosen to
                ensure service efficiency and availability. We adhere to high privacy and security standards to protect
                your data, regardless of server location.</p>
        </div>

        <div class="section">
            <h2>5. Data Retention</h2>
            <p>We retain your personal information while your account remains active. Once you request the deletion of
                your account, your data will be permanently erased unless retention is required to comply with legal
                obligations.</p>
        </div>

        <div class="section">
            <h2>6. User Choices and Control</h2>
            <p>Shhask provides you with control over your personal information through the following options:</p>
            <ul>
                <li>Edit your full name, username, and email address at any time via your account settings</li>
                <li>Request the complete deletion of your account and personal data</li>
            </ul>
        </div>

        <div class="section">
            <h2>7. Data Security</h2>
            <p>We have implemented technical and administrative measures to safeguard your personal information against
                unauthorized access, loss, or alteration. While we utilize advanced security systems, absolute
                protection cannot be guaranteed due to the nature of technology.</p>
        </div>

        <div class="section">
            <h2>8. Use of Local Storage</h2>
            <p>The Application uses local storage technologies on your device to enhance user experience. These
                technologies do not track or collect additional information about your activity.</p>
        </div>

        <div class="section">
            <h2>9. Children's Privacy</h2>
            <p>Shhask is intended for users aged 13 and older. We do not knowingly collect information from children
                under this age. If you are a parent or guardian and believe your child has provided us with personal
                information, please contact us to have it removed.</p>
        </div>

        <div class="section">
            <h2>10. External Links</h2>
            <p>Shhask may include links to third-party websites. We are not responsible for the privacy policies or
                practices of these external sites, and we recommend reviewing their policies before providing any
                personal information.</p>
        </div>

        <div class="section">
            <h2>11. Changes to This Privacy Policy</h2>
            <p>We reserve the right to update this Privacy Policy at any time. Any significant changes will be
                communicated through the application or via electronic means, indicating the date of the latest update.
            </p>
        </div>

        <div class="contact-info">
            <h2>12. Contact Us</h2>
            <p>If you have any questions or concerns about this Privacy Policy, you can reach us at:</p>
            <p>Email: <a href="mailto:support@shhask.com">support@shhask.com</a></p>
            <p>Website: <a href="http://www.shhask.com">www.shhask.com</a></p>
        </div>
    </div>
</body>

</html>
