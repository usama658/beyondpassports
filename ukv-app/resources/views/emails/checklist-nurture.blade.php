<x-mail::message>
# Still planning your {{ $destination }} trip?

A little while ago you built a document checklist with us. It's still saved — pick up where you left off any time.

<x-mail::button :url="$savedLink">
View your saved checklist
</x-mail::button>

When you're ready, we can take it from here: prepare and check your application, book the appointment, and confirm every requirement before you pay. No approval is ever guaranteed, but nothing gets submitted until it's right.

<x-mail::button :url="$applyUrl" color="success">
Start my application
</x-mail::button>

Prefer to talk it through first? Just reply to this email.

Thanks,
Beyond Passports

<x-mail::subcopy>
You're receiving this because you asked us to keep you updated when you built your checklist. Not interested any more? Reply "unsubscribe" and we'll stop. Beyond Passports is an independent service and is not a government website.
</x-mail::subcopy>
</x-mail::message>
