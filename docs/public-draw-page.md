# Public September draw page

## Implementation

`/september-draw` is a code-authored Waaseyaa page, like the current homepage.
It is not a standalone static file and is not yet edited through the CMS.
`AppServiceProvider` registers the named route `jgs.september_draw` through the
framework's `RouteBuilder`, ahead of the generated generic `/{slug}` route.

The CMS published-content and governed-authoring recipes remain installed and
unchanged. Moving this page into CMS authoring later requires deliberate content
and template integration; do not silently create a second page with this slug.

## Files

- `src/Controller/DrawController.php`: response and page-specific canonical/social metadata.
- `src/Controller/StorefrontRenderer.php`: allowlisted home/draw templates and shared shell.
- `templates/partials/site-header.html.twig`, `site-footer.html.twig`: shared JGS shell.
- `templates/september-draw.html.twig`: public copy, prize information, poster and contact links.
- `public/assets/draw.css`: responsive page styles.
- `public/assets/images/jgs-september-draw.png`: clean poster and page-specific social image.
- `public/assets/images/jgs-vehicle-detailing.svg`: local vector detailing accent.
- `tests/Unit/Controller/DrawControllerTest.php`: public copy, metadata, shell, linking and template allowlist.

The homepage includes a visible draw link and the sitemap includes its canonical
URL. Navigation returns to the correct homepage sections from either page.

## Content boundary

The page introduces the September draw, detailing certificate and JGS basket,
with the October 1 Facebook Live plan. It says full prize and entry information
is on the way. It does not collect entries, invent prize values or eligibility,
or publish internal review notes. No CMS record or database migration is needed.

When the remaining information is supplied, update the public template and
poster together. After the campaign, replace the active announcement with a
closed-draw update and remove the seasonal homepage invitation.

## Verify and deploy

Run the controller tests, `tests/Architecture/SiteContractTest.php`, strict
`site:doctor`, and `bin/maintenance/site-verify` in a native Linux checkout.
Do not use `/mnt/c` file modes as evidence of generated-file permission drift.
The offline verification copy must not contain production secrets or data.

Deploy through the existing Pi infrastructure's exact-revision `jgs` promotion,
not by copying private drafts into the web root. Update only the JGS application
pin and its route probes. Preserve the prior image and the normal SQLite
snapshot/secret/preflight gates. Confirm HTTP 200 for `/`, `/september-draw`,
`/sitemap.xml`, and the page's image/styles; check page-specific metadata and
absence of internal notes. Sending a message to anyone is not part of deployment.
