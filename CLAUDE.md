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

## 版数

★★ **配布4本（woo-modal-block / woo-checkout-colorbox / woo-hit-orderlist / widget-shortcode-tools）は
版数を揃える。単独で上げない。**

版数は **2箇所**（ヘッダの `Version:` と `$clbx_version`）。
★ `$clbx_version` は `wp_enqueue_script` の**キャッシュバスターを兼ねる**ので、
ヘッダだけ上げると旧 JS が残る。
