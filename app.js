const puppeteer = require('puppeteer-core');
const http = require('http');

const ADMIN_URL = "http://10.11.77.66/i_am_admin_hhh.php";
const BOTTOKEN = process.env.BOTTOKEN || "";

var num = 0;

const get_urls = async () => {
    try {
        http.get(`${ADMIN_URL}?token=${BOTTOKEN}`, resp => {
            const {
                statusCode
            } = resp;
            if (statusCode !== 200) {
                console.log("[*] Bot API", `[${statusCode}] ${ADMIN_URL}/?token=${BOTTOKEN}`);
                return false;
            }
            resp.setEncoding('utf8');
            let rawData = '';
            resp.on('data', (chunk) => {
                rawData += chunk;
            });
            resp.on('end', () => {
                var pd = {};
                try {
                    pd = JSON.parse(rawData);
                } catch (ee) {
                    console.log(rawData)
                }
                if (pd.code == 0)
                    open_payload_url(pd.id, pd.url);
                else
                    console.log("[*] Bot API", `[${statusCode}] ${pd.msg || "JSON Parse Error"}`);
            });
        });
    } catch (e) {
        console.error("[-] Get Urls\n", e.stack)
    }

    setTimeout(() => {
        get_urls();
    }, 2000);
}

const open_payload_url = async (user, url) => {
    let _num = ++num;
    console.log(`[${user}][${_num}] [+] Open Page ${url}`);
    let page;
    try {
        page = await browser.newPage();

        await page.on('error', err => {
            console.error(`[${user}][${_num}] [#] Error!`, err);
        });

        await page.on('pageerror', msg => {
            console.error(`[${user}][${_num}] [-] Page error : `, msg);
        })

        await page.on('dialog', async dialog => {
            console.debug(`[#] Dialog : [${dialog.type()}] "${dialog.message()}" ${dialog.defaultValue() || ""}`);
            await dialog.dismiss();
        });

        await page.on('console', async msg => {
            msg.args().forEach(arg => {
                arg.jsonValue().then(_arg => {
                    console.log(`[$] Console : `, _arg)
                });
            });
        });

        await page.on('requestfailed', req => {
            console.error(`[-] Request failed : ${req.url()} ${req.failure().errorText}`);
        })

        await page.once('load', async () => {
            console.log('[+] Page loaded!')
            const data = await page.content();
            console.log(data);
        });

        // ===== Custom Action =====
        // 自定义页面操作

        await page.setCookie({
            name: "flag",
            value: process.env.FLAG || "no flag",
            domain: "10.11.77.66",
            sameSite: "Lax"
        }, {
                name: "BOTTOKEN",
                value: BOTTOKEN,
                httpOnly: true,
                domain: "10.11.77.66"
            });

        await page.goto(url, {
            timeout: 5000,
            waitUntil: 'load'
        });

        await page.waitFor(5 * 1000);

        // =========================

    } catch (e) {
        console.error("[-] Page open_payload_url\n", e.stack)
    }

    page.close();
    console.log(`[${user}][${_num}] [+] Close...`)
}

var browser;

(async () => {

    // 启动 Chrome
    browser = await puppeteer.launch({
        executablePath: '/usr/bin/chromium-browser',
        args: [
            '--headless',
            '--disable-dev-shm-usage',
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-gpu',
            '--no-gpu',
            '--disable-default-apps',
            '--disable-translate',
            '--disable-device-discovery-notifications',
            '--disable-software-rasterizer'
            // '--disable-xss-auditor'
        ],
        userDataDir: '/home/bot/data/',
        // 忽略 HTTPS 错误
        ignoreHTTPSErrors: true
    });

    // 创建一个匿名的浏览器上下文
    // browser = await browser.createIncognitoBrowserContext();

    console.log("[+] Browser", "Launch success!");

    get_urls();

    // console.log("[+] Browser", "Close success!");
    // await browser.close();
})();