## レビュー工程に大（シニアエンジニア）を追加する

このリポジトリでは、安藤（`vk-code-reviewer`）のレビューのあと、**PR を作成する前に**
大（`etbs-senior-wp`）の監査を必ず通すこと。大は etbs の申し送りと過去に踏んだ罠に照らして
「リリースできる形になっているか」を見る担当で、安藤の一般的なコード品質レビューとは層が違う。

- `Agent` ツールで `subagent_type: etbs-senior-wp`、`name: etbs-senior-wp`、
  **`run_in_background: false`** で起動する
- **`isolation: "worktree"` は使えるなら付ける**（付けないと起動応答は「成功」と返るのに
  一度も作業せず待機状態に入ることがある）。ただし ★★ **作業ディレクトリが git リポジトリでないと使えない。**
  その場合は **isolation なしで起動してよい**（2026-08-19 実績あり）。
  **見分け方は起動応答の形**——`output_file` 付きの正常形なら動いている
- prompt には対象リポジトリ・ブランチ・差分（または PR 番号）を渡す
- 大には **出力の末尾に `監査結果: PASS` または `監査結果: FAIL` を必ず書くよう指示する**
  （★ 大の定義ファイルには出力形式の指定が無いため、指示しないと合否を機械判定できない）
- `監査結果: PASS` を受け取るまで PR を作成しない。`FAIL` なら和田へ差し戻して再監査する

## 宣言（Requires）の方針

★★ `Requires at least` / `Requires PHP` は**実在する下限があるときだけ書く。無ければ書かない。**
**他のプラグインと横並びで揃えない。** 本体ヘッダだけでなく `readme` にも書かない。

★★★ **このプラグインは無宣言のまま据え置くと決めた（2026-08-19）。**
4本の中で最も深刻だった欠陥（設定画面から全顧客の checkout ページで走る JS を注入できた）の
修正が 1.1.0 で出ており、**無宣言だからこそ全個体に届いている**。ここに下限を書くと配信範囲を狭める。

- 過剰宣言は WordPress が `validate_plugin_requirements()` で**有効化そのものを拒否**する
- 更新の見え方は宣言で違う。`Requires at least` は PUC が `requires` を渡さないため
  **更新リンクは出るが押すと失敗**（`Plugin_Upgrader::check_package()`）。
  `Requires PHP` は PUC が渡すため**更新リンク自体が出ない**


★★★ **この判断は `Requires PHP` にも及ぶ（無宣言のまま据え置く）。** 2026-08-25 に
task-queue #111 で「`Requires PHP` を書かない判断として合意した記録が無い」と読まれかけたため明記する。
節の見出しが `Requires at least` の話に見えるが、**両方のヘッダが対象**。

実測（2026-08-25）: PHP 7.4.30 で同梱 PUC 込みの全ファイル、PHP **7.3.5** で自前コード全ファイルが
`php -l` を通る。→ **「書かないと構文エラーで白画面」という害は実在しない。**
一方、新しく書けば PHP 7.3 以下の個体には**以後の更新が永久に届かなくなる**。
書かない側の害がゼロで、書く側の害が非可逆なので、無宣言を維持する。

★★ **「据え置き」と「新規に足す」は別問題**（2026-08-25 / task-queue #111 で再確認）。
既に宣言している版を据え置いても新たに締め出す個体は生まれないが、**無宣言のプラグインに
`Requires PHP` を新しく足すと、いま更新が届いている個体を以後届かなくする**。
`woo-checkout-colorbox` と `widget-shortcode-tools` が無宣言なのは、この理由による意図的な判断。
**8本で揃えにこないこと。**

## CI（2026-08-28 導入・task-queue #155 / issue #3）

PR ごとに GitHub Actions で3ジョブ走る。定義は `.github/workflows/ci.yml`。
★ **原本 `etbsjp/widget-shortcode-tools` の `dist` `3857ad4` とバイト一致。書き換えないこと。**

| ジョブ | 中身 |
|---|---|
| `php -l (PHP 7.4)` / `(PHP 8.3)` | 追跡している全 `.php` の構文チェック |
| `PHPCS (WordPress-Extra, changed lines)` | WPCS を **PR の差分行だけ**に適用（`sirbrillig/phpcs-changed`） |

### 決定済み（変えないこと）

- ★★ **既存コードの指摘は直さない。** 2026-08-28 実測で `WordPress-Extra` に対し **65 ERROR / 22 WARNING**（追跡している自前コードのみ）。
  `dist` は PUC の配信先なので、整形のための版数上げ＝配信を起こしたくない。だから `phpcs-changed` で**変更行のみ**を必須にしている
- ★★★ **`phpcbf` を走らせないこと。** 一度走ると自動修正が入り、上の前提が丸ごと壊れる
- **検査ツールは同梱しない。** `composer install` で入る（`composer.json` は `require-dev` のみ）。
  生成物 `vendor/` は `.gitignore` 済み・配布物からは `export-ignore` 済み
- **standard は `WordPress-Extra`。** `WordPress-Core` は未エスケープ出力（XSS）も nonce 未検証も
  **検出しない**（陽性対照で実測）。フル `WordPress` との差は docblock の書式だけなので採らない
- **起動条件は `pull_request` の無条件実行。** `run-ci` ラベル条件にしない。
  vk-agents の `ci.md` はエージェントが CI を起動しない運用だが、
  「リポジトリ側の設定で自動実行される場合」は例外。ラベル運用だと自動フローの PR で CI が一度も走らない
- `inc/plugin-update-checker/` は第三者コードなので `.phpcs.xml.dist` で検査対象から除外
- **third-party action はタグ固定のまま**（公開 repo・secrets 未使用・`contents: read` のみ・成果物を生成しない）。
  ★ secrets を持つか配布物を生成するようになったら SHA 固定を見直す

### ★★★ CI が守るのは PHP 7.4 まで。7.3 は守られていない

下の「宣言（Requires）の方針」は `Requires PHP` を**無宣言で据え置く**判断をしており、その根拠は
「PHP 7.3.5 で `php -l` が通る」という実測1本だけ。**CI の matrix は `['7.4','8.3']` なので、この根拠は自動では守られていない。**

★ 次の構文を書くと **PHP 7.3 の個体は白画面**になる。しかも `Requires PHP` が無いので
**WordPress は警告を出さずに更新を配る**（更新リンクを止める `requires_php` を PUC に渡さないため）:
アロー関数 `fn() =>` / 型付きプロパティ / `??=` / `match` / ヌル安全演算子 `?->`

★★ **PHPCompatibility では代用できない。** 2026-08-27 の実測で、安定版 9.3.5（2019年）は
アロー関数・`match`・`?->` を**検出しない**。「PHPCompatibility が緑」は「7.3 で動く」の証明にならない。

→ 7.3 で動くことを保証したい変更を書いたら、その場で PHP 7.3 の `php -l` を通すこと。

### ★ CI に触るときの検証

**「Error 0 で緑」を成果にしないこと。** `.php` を変更しない PR では PHPCS は対象0件で自明に緑になる。
検査が動いていることは**陽性対照**でしか言えない ― 使い捨てブランチに `echo $_GET['probe'];` を1行足し、
`PHPCS` が赤くなり指摘がその行を指すことを確認する。

★★ **配布物を検証するときは「CI 一式が入っていないこと」だけを見ないこと。**
それだと**消えてはいけない物が消えたことに気づけない**。現行 `dist` とファイル一覧を突合する。

```sh
git archive --format=tar HEAD | tar -t | sort > /tmp/pr.txt
git archive --format=tar dist | tar -t | sort > /tmp/dist.txt
comm -3 /tmp/dist.txt /tmp/pr.txt     # ★ 空であること
grep -v '/$' /tmp/pr.txt | grep -cE 'plugin-update-checker/(vendor/|composer\.json)'   # ★ 4
```

★ `grep -v '/$'` を忘れると `vendor/` のディレクトリ行も数えて 5 になる。

### ★★ 素の `phpcs` を叩くと数字が狂う

`.phpcs.xml.dist` の `<file>.</file>` は**作業ディレクトリを全部掃く**。`phpcs` は `.gitignore` を尊重しないので、
`.claude/worktrees/` のコピーや `-old` 系のバックアップまで数えてしまう。

2026-08-28 に実測した例（excelrange）: 追跡ファイルのみなら **81 E / 4 W** だが、
`.gitignore` 済みの `inc/tools/import-excel-old.php` を拾うと **126 E / 8 W** になる。

→ 上の基準値を測り直すときは、対象を `git ls-files '*.php'` に限定すること。
**CI では発火しない**（`phpcs-changed` に `git diff` 由来の明示ファイルだけを渡すため）。

### dist への直 push も検査する

`on:` は `pull_request` と `push: branches: [dist]` の2つ。`dist` は PUC の配信元で、版数上げは
人が直接 push する運用なので、PR を通らない変更がそのまま利用者へ配られる経路が残っていた。

★ **これは鍵ではなく火災報知器。** CI は push の**後**に走るので、壊れたコードは一度 `dist` に載る。
配信そのものを止めたいなら branch protection の必須チェック化が要るが、
その場合は**版数上げの直 push も塞がる**（2026-08-28 時点で Classic・Rulesets とも未設定）。

★ push では `phpcs-changed` は走らない（比較の基準になるブランチが無いため）。走るのは `php -l` の2つだけ。

## アンインストール

★ `uninstall.php` の方針は**案A**（task-queue #108）。判定は3分類。

| 利用者が作ったコンテンツ（投稿・投稿メタ） | 利用者が設定した値（オプション） | 一時状態・自分が仕掛けた cron |
|---|---|---|
| **消さない** | **消さない** | **消す** |

理由は害の非対称性。消さないことの害は「DB に少量のレコードが残る」だけだが、消すことの害は
復旧不可能。迷ったら残す側に倒す。

このプラグインでの当てはめ:

- **残す** … オプション `woochksetting`（`tools/setting.php:131`。利用者が設定した値）
- **消す** … 該当なし（独自テーブルも cron も持たない）

★ 配布8本すべてがこの3分類で説明できる状態にしてある。テーブルと cron を持つのは editlock だけ、
一時状態のオプションを持つのは pageguard だけで、そこだけが「消す」に該当する。
**他のプラグインで「何も消していない」のは判断の結果であって書き忘れではない。**
横並びで「消す」側へ揃えにこないこと。

## 版数

★★ **配布4本（woo-modal-block / woo-checkout-colorbox / woo-hit-orderlist / widget-shortcode-tools）は
版数を揃える。単独で上げない。**

版数は **2箇所**（ヘッダの `Version:` と `$clbx_version`）。
★ `$clbx_version` は `wp_enqueue_script` の**キャッシュバスターを兼ねる**ので、
ヘッダだけ上げると旧 JS が残る。
