#!/usr/bin/env python3
"""
Two fixture sites for testing dry_run.py, shaped like the real migration.

OLD (:8801) — GoDaddy-like:
  - sitemap.xml is an index pointing at two children
  - four /m/* account pages that must be excluded
  - NO trailing slashes
  - most titles carry an appended " | Wellspring Health" suffix (CMS artifact)
  - one title contains a NO-BREAK SPACE
  - two pages share an identical description
  - one page is a JS shell with no <title>
  - one page (/events) has no counterpart on the new site

NEW (:8802) — WordPress-like:
  - trailing slashes canonical; bare paths 301 to the slashed form
  - wp-sitemap.xml includes pages with no old counterpart
  - robots.txt blocks Googlebot (the staging trap)
  - no meta descriptions at all (pre-import baseline)
  - one page deliberately emits TWO <title> tags
  - /events/ returns 404
"""

import threading
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

OLD_PORT, NEW_PORT = 8801, 8802
SUFFIX = " | Wellspring Health"


def doc(title, desc=None, canonical=None, extra=""):
    d = f'<meta name="description" content="{desc}">' if desc else ""
    c = f'<link rel="canonical" href="{canonical}"/>' if canonical else ""
    return (
        f'<!doctype html><html><head><meta charset="utf-8">'
        f"<title>{title}</title>{d}{c}{extra}"
        f"</head><body><p>content</p></body></html>"
    ).encode("utf-8")


# --------------------------------------------------------------------- OLD site
OLD_BASE = f"http://127.0.0.1:{OLD_PORT}"
SHARED_DESC = "Acupuncture and Traditional Chinese Medicine in Calgary."

OLD_PAGES = {
    "/": doc(
        "Wellspring Health Acupuncture & Traditional Chinese Medicine (TCM) Clinic Calgary",
        "Calgary acupuncture and TCM for pain, stress, sleep and much more.",
        f"{OLD_BASE}/",
    ),
    # NBSP between "Pain" and "Relief", plus the CMS suffix.
    "/about": doc(
        f"About Us{SUFFIX}",
        "Meet Dr. Laura Cowburn, Registered Acupuncturist and Doctor of TCM in Calgary.",
        f"{OLD_BASE}/about",
    ),
    "/what-we-treat": doc(
        f"Conditions We Treat{SUFFIX}",
        "Acupuncture &amp; TCM for pain relief, women&#8217;s health and more.",
        f"{OLD_BASE}/what-we-treat",
    ),
    "/clinic-cases": doc(f"Clinic Cases{SUFFIX}", SHARED_DESC, f"{OLD_BASE}/clinic-cases"),
    "/contact": doc(f"Contact{SUFFIX}", SHARED_DESC, f"{OLD_BASE}/contact"),
    "/book-appointments": doc(f"Book an Appointment{SUFFIX}", None, f"{OLD_BASE}/book-appointments"),
    "/events": doc(f"Events{SUFFIX}", "Upcoming workshops and clinic events.", f"{OLD_BASE}/events"),
    # JS shell: no title at all.
    "/privacy-policy": b'<!doctype html><html><head><meta charset="utf-8"></head>'
                       b'<body><div id="app"></div></body></html>',
}
for p in ("/m/login", "/m/reset", "/m/create", "/m/create-account"):
    OLD_PAGES[p] = doc("Sign In", "account page")

OLD_SITEMAP_INDEX = f"""<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<sitemap><loc>{OLD_BASE}/sitemap.website.xml</loc></sitemap>
<sitemap><loc>{OLD_BASE}/sitemap.ola.xml</loc></sitemap>
</sitemapindex>""".encode()

_web = [p for p in OLD_PAGES if not p.startswith("/m/")] + ["/m/login", "/m/reset", "/m/create", "/m/create-account"]
OLD_SITEMAP_WEB = (
    '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
    + "".join(f"<url><loc>{OLD_BASE}{p}</loc></url>" for p in _web)
    + "</urlset>"
).encode()
OLD_SITEMAP_OLA = (
    '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
    f"<url><loc>{OLD_BASE}</loc></url></urlset>"
).encode()
OLD_ROBOTS = b"User-agent: *\nAllow: /\n"


# --------------------------------------------------------------------- NEW site
NEW_BASE = f"http://127.0.0.1:{NEW_PORT}"

NEW_PAGES = {
    "/": doc("Wellspring Health – Acupuncture & Traditional Chinese Medicine", None, f"{NEW_BASE}/"),
    "/about/": doc("About – Wellspring Health", None, f"{NEW_BASE}/about/"),
    "/what-we-treat/": doc("What We Treat – Wellspring Health", None, f"{NEW_BASE}/what-we-treat/"),
    "/clinic-cases/": doc("Clinic Cases – Wellspring Health", None, f"{NEW_BASE}/clinic-cases/"),
    "/contact/": doc("Contact – Wellspring Health", None, f"{NEW_BASE}/contact/"),
    "/book/": doc("Book Appointments – Wellspring Health", None, f"{NEW_BASE}/book/"),
    # Deliberate defect: two title tags.
    "/privacy-policy/": b'<!doctype html><html><head><meta charset="utf-8">'
                        b"<title>Privacy Policy \xe2\x80\x93 Wellspring Health</title>"
                        b"<title>Privacy Policy</title></head><body></body></html>",
    # Orphans: exist here, nothing to port.
    "/services/": doc("Services – Wellspring Health", None, f"{NEW_BASE}/services/"),
    "/what-we-treat/pain-relief/": doc("Pain Relief – Wellspring Health", None, f"{NEW_BASE}/what-we-treat/pain-relief/"),
    "/what-we-treat/womens-health/": doc("Women's Health – Wellspring Health", None, f"{NEW_BASE}/what-we-treat/womens-health/"),
}

NEW_SITEMAP = (
    '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
    + "".join(f"<url><loc>{NEW_BASE}{p}</loc></url>" for p in NEW_PAGES)
    + "</urlset>"
).encode()
NEW_ROBOTS = b"User-agent: Googlebot\nDisallow: /\n\nUser-agent: *\nAllow: /\n"


class OldHandler(BaseHTTPRequestHandler):
    def log_message(self, *a):
        pass

    def do_GET(self):
        p = self.path.split("?")[0]
        routes = {
            "/sitemap.xml": (200, "application/xml", OLD_SITEMAP_INDEX),
            "/sitemap.website.xml": (200, "application/xml", OLD_SITEMAP_WEB),
            "/sitemap.ola.xml": (200, "application/xml", OLD_SITEMAP_OLA),
            "/robots.txt": (200, "text/plain", OLD_ROBOTS),
        }
        if p in routes:
            code, ctype, body = routes[p]
        elif p in OLD_PAGES:
            code, ctype, body = 200, "text/html; charset=utf-8", OLD_PAGES[p]
        elif p.rstrip("/") in OLD_PAGES and p != "/":
            # Old site serves bare paths; a slashed request 301s to bare.
            self.send_response(301)
            self.send_header("Location", p.rstrip("/"))
            self.end_headers()
            return
        else:
            code, ctype, body = 404, "text/html", b"<html><head><title>Not Found</title></head></html>"
        self.send_response(code)
        self.send_header("Content-Type", ctype)
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)


class NewHandler(BaseHTTPRequestHandler):
    def log_message(self, *a):
        pass

    def do_GET(self):
        p = self.path.split("?")[0]
        if p == "/wp-sitemap.xml":
            code, ctype, body = 200, "application/xml", NEW_SITEMAP
        elif p == "/robots.txt":
            code, ctype, body = 200, "text/plain", NEW_ROBOTS
        elif p in NEW_PAGES:
            code, ctype, body = 200, "text/html; charset=utf-8", NEW_PAGES[p]
        elif p != "/" and (p + "/") in NEW_PAGES:
            # WordPress canonicalises to the trailing slash.
            self.send_response(301)
            self.send_header("Location", p + "/")
            self.end_headers()
            return
        else:
            code, ctype, body = 404, "text/html; charset=utf-8", doc("Page not found – Wellspring Health")
        self.send_response(code)
        self.send_header("Content-Type", ctype)
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)


def serve():
    old = ThreadingHTTPServer(("127.0.0.1", OLD_PORT), OldHandler)
    new = ThreadingHTTPServer(("127.0.0.1", NEW_PORT), NewHandler)
    threading.Thread(target=old.serve_forever, daemon=True).start()
    threading.Thread(target=new.serve_forever, daemon=True).start()
    return old, new


if __name__ == "__main__":
    serve()
    print(f"OLD {OLD_BASE}\nNEW {NEW_BASE}\nserving; ctrl-c to stop")
    try:
        threading.Event().wait()
    except KeyboardInterrupt:
        pass
