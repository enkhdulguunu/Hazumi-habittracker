[English](README.md) | [日本語](README.ja.md)

# Hazumi

Hazumi は、習慣、To-do タスク、カレンダー予定、タイムブロック、ノートを管理するための PHP / MySQL 製 Web アプリケーションです。アプリケーションはすでにサーバーへ公開されており、ブラウザから直接利用できます。

## 公開サイト

Hazumi は以下の URL から利用できます。

[https://gict.xsrv.jp/dulguun_1224/Hazumi/index.html]

## 目的

Hazumi は、日々の生産性データを1つの場所で整理するためのアプリケーションです。習慣、タスク、予定、ノートが別々のツールに分散しがちな問題に対し、アカウント単位でまとめて管理できる構成になっています。

現在の実装では、主に以下を扱えます。

- 継続的な習慣と達成日の記録
- 個人To-doタスクの管理
- 日付付きカレンダー予定とダッシュボードのタイムブロック管理
- 個人ノートの保存

### 完了している機能

- Home、About、Feature、Join セクションを持つ公開ランディングページ
- ユーザー登録とログイン
- PHP セッションによる認証
- PHP の `password_hash()` と `password_verify()` を使用したパスワード処理
- PHP セッションを破棄し、ランディングページへ戻るログアウト処理
- 習慣、To-do、進捗、タイムブロックを表示するログイン後ダッシュボード
- プロフィール編集と任意のパスワード変更ができるSettingsページ
- MySQL保存のTo-do List
  - タスク作成
  - タスク本文の編集
  - 完了・未完了の切り替え
  - 個別削除
  - 現在のユーザーの全タスク、または完了済みタスクの削除
  - ダッシュボードとTo-do Listページで同じ保存データを共有
- MySQL保存のHabit Tracker
  - 新規ユーザー向けの初期習慣データ
  - 習慣の追加、名称変更、削除
  - 日付ごとの達成チェック切り替え
  - 習慣トラッカーのタイトル編集
  - ダッシュボードとHabit Trackerページで同じ保存データを共有
- MySQL保存のCalendar
  - 月、週、日、年の表示モード
  - 日付付きタスクの追加と削除
  - ダッシュボードのタイムブロックからカレンダー枠のデータを更新
- MySQL保存のNotes
  - ノートの作成、編集、検索、削除
  - Notes ページでの自動保存に近い編集動作
- TermsとPrivacy Policy ページ
- `assets/js/` 以下にページ別JavaScriptファイルを配置

## 使用技術・依存関係

- PHP、PHP セッション、PDO
- MySQL / MariaDB
- HTML、CSS、Vanilla JavaScript
- ブラウザから API へ通信するための Fetch API
- Google Fonts
  - Modak
  - Nunito
  - Fredoka One

## ディレクトリ構成

```text
Hazumi/
├── api/
│   ├── auth/
│   ├── calendar/
│   ├── habits/
│   ├── notes/
│   ├── timeblocks/
│   ├── todos/
│   └── common.php
├── assets/js/
├── config/
│   └── database.php
├── images/
├── sql/
│   └── schema.sql
├── index.html
├── login.php
├── signup.php
├── dashboard.php
├── habittracker.html
├── todolist.html
├── calendar.html
├── notes.html
├── setting.php
├── terms.html
└── privacy.html
```

## 公開サイトの使い方

1. ブラウザで [Hazumi](https://gict.xsrv.jp/dulguun_1224/Hazumi/) を開きます。
2. **Sign up** からアカウントを作成します。
3. 登録したメールアドレスとパスワードでログインします。
4. ダッシュボードで習慣の進捗、To-do、タイムブロックを確認します。
5. サイドバーから Habit Tracker、To-do List、Calendar、Notes、Settings を開きます。
6. Settings でプロフィール情報の更新やパスワード変更を行います。
7. Logout でセッションを終了します。