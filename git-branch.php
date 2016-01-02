<?php
/*
第三篇 Git进阶 —— 3.3 Git分支
3.3 Git分支

几乎每一种版本控制系统都以某种形式支持分支。使用分支意味着你可以从开发主线上分离开来，然后在不影响主线的同时继续工作。在很多版本控制系统中，这是个昂贵的过程，常常需要创建一个源代码目录的完整副本，对大型项目来说会花费很长时间。

有人把 Git 的分支模型称为“必杀技特性”，而正是因为它，将 Git 从版本控制系统家族里区分出来。

Git 有何特别之处呢？Git 的分支可谓是难以置信的轻量级，它的新建操作几乎可以在瞬间完成，并且在不同分支间切换起来也差不多一样快。和许多其他版本控制系统不同，Git 鼓励在工作流程中频繁使用分支与合并，哪怕一天之内进行许多次都没有关系。理解分支的概念并熟练运用后，你才会意识到为什么 Git 是一个如此强大而独特的工具，并从此真正改变你的开发方式。

3.3.1 何谓分支

为了理解 Git 分支的实现方式，我们需要回顾一下 Git 是如何储存数据的。Git 保存的不是文件差异或者变化量，而只是一系列文件快照。

在 Git 中提交时，会保存一个提交（commit）对象，它包含一个指向暂存内容快照的指针，作者和相关附属信息，以及一定数量（也可能没有）指向该提交对象直接祖先的指针：第一次提交是没有直接祖先的，普通提交有一个祖先，由两个或多个分支合并产生的提交则有多个祖先。

Git 中的分支，其实本质上仅仅是个指向 commit 对象的可变指针。Git 会使用 master 作为分支的默认名字。在若干次提交后，你其实已经有了一个指向最后一次提交对象的 master 分支，它在每次提交的时候都会自动向前移动。
3.3.2 新建分支

Git新建分支的方法很简单，通过git branch 命令即可创建于一个新的分支 $ git branch testing

Git里有名为 HEAD 的特别指针，他记录着指向的分支，在新建完testing 分支后，我们通过查看.git/refs/heads/testing文件的内容，发现与mater分支引用文件的内容相同，这就说明，新建分支只是使得HEAD指针指向了testing分支。

$ cat .git/refs/heads/testing
1924a0c69cc38e3ee2b45cfd41bdbdce3870f08f

运行 git branch 命令，仅仅是建立了一个新的分支，但不会自动切换到这个分支中去，所以在这个例子中，我们依然还在 master 分支里工作。

$ git branch
* master
  testing

要切换到其他分支，可以执行 git checkout 命令。我们现在转换到新建的 testing 分支： $ git checkout testing

这样 HEAD 就指向了 testing 分支。

$ git checkout testing
切换到分支 'testing'
$ git branch
  master
* testing

3.3.3 合并分支

现在分支已经成功创建，我们可以在testing 分支上开发新功能而不影响master 分支，比如我们新建文件 new-tools.txt并提交：

$ touch new-tools.txt
$ git add new-tools.txt 
$ git commit -m "add new-tools.txt"
[testing 555be0e] add new-tools.txt
 1 file changed, 0 insertions(+), 0 deletions(-)
 create mode 100644 new-tools.txt

如果分支开发完成，我们可以用 git merge 命令来进行合并：

$ git checkout master
切换到分支 'master'
$ git merge testing
更新 1924a0c..555be0e
Fast-forward
 new-tools.txt | 0
 1 file changed, 0 insertions(+), 0 deletions(-)
 create mode 100644 new-tools.txt

请注意，合并时出现了 “Fast forward”（快进）提示。由于当前 master 分支所在的 commit 是要并入的 testing 分支的直接上游，Git 只需把指针直接右移。换句话说，如果顺着一个分支走下去可以到达另一个分支，那么 Git 在合并两者时，只会简单地把指针前移，因为没有什么分歧需要解决，所以这个过程叫做快进（Fast forward）。现在的目录变为当前 master 分支指向的 commit 所对应的快照。
3.3.4 删除分支

在testing 分支成功合并到master分支后，现在testing分支与master分支指向相同，已经没什么用了，可以直接删除，使用git branch 命令的 –d 选项表示删除分支：

$ git branch -d testing
已删除分支 testing（曾为 555be0e）

现在只剩下master分支了：

$ git branch
* master

3.3.5 合并冲突

在分支合并时，如果没有冲突，则会基本合并，在合并后删除开发分支即可。

但经常在合并时会遇到冲突，如果你修改了两个待合并分支里同一个文件的同一部分，Git 就无法干净地把两者合到一起，在这种情况下，基本只能手动解决冲突。

$ git merge test
自动合并 index.html
冲突（添加/添加）：合并冲突于 index.html
自动合并失败，修正冲突然后提交修正的结果。

Git 作了合并，但没有提交，它会停下来等你解决冲突。要看看哪些文件在合并时发生冲突，可以用 git status 查阅：

$ git status index.html
位于分支 master
您有尚未合并的路径。
  （解决冲突并运行 "git commit"）

未合并的路径：
  （使用 "git add <file>..." 标记解决方案）

    双方添加： index.html

修改尚未加入提交（使用 "git add" 和/或 "git commit -a"）

查看冲突文件，可以看到冲突的地方已经用符号<<<<<<<、>>>>>>>>、=========标出来了：

$ cat index.html
<<<<<<< HEAD
<div id="footer">contact : email.support@github.com</div>
=======
<div id="footer"> please contact us at support@github.com
</div>

>>>>>>> test

可以看到 ======= 隔开的上半部分，是 HEAD（即 master 分支，在运行 merge 命令时检出的分支）中的内容，下半部分是在 test分支中的内容。解决冲突的办法无非是二者选其一或者由你亲自整合到一起。比如你可以通过把这段内容替换为下面这样来解决：

<div id="footer"> please contact us at support@github.com
contact : email.support@github.com
</div>

这个解决方案各采纳了两个分支中的一部分内容，而且我还删除了 <<<<<<<，=======，和>>>>>>> 这些行。在解决了所有文件里的所有冲突后，运行 git add 将把它们标记为已解决（resolved）。因为一旦暂存，就表示冲突已经解决。

冲突解决后提交即可。

$ git add index.html
$ git commit -m "merge"

3.3.6 分支管理

 git branch 命令不仅仅能创建和删除分支，如果不加任何参数，它会给出当前所有分支的清单。

$ git branch
* master
  test
  testing
   git branch -merge 查看哪些分支已被并入当前分支。
$ git branch --merge
* master
  test
   git branch --no-merged 查看尚未合并的工作。
$ git branch --no-merged
  testing
   git branch -D testing强制删除未合并的分支。
$ git branch -D testing
已删除分支 testing（曾为 b3b779e）。

3.3.7 远程分支
3.3.7.1 推送

要想和其他人分享某个分支，你需要把它推送到一个你拥有写权限的远程仓库。你的本地分支不会被自动同步到你引入的远程分支中，除非你明确执行推送操作。换句话说，对于无意分享的，你尽可以保留为私人分支，而只推送那些协同工作的特性分支。

如果你有个叫 serverfix 的分支需要和他人一起开发，可以运行

git push (远程仓库名) (分支名)：
$ git push origin serverfix Counting objects: 20, done.
Compressing objects: 100% (14/14), done.
Writing objects: 100% (15/15), 1.74 KiB, done.
Total 15 (delta 5), reused 0 (delta 0)
To git@github.com:schacon/simplegit.git
* [new branch]  serverfix -> serverfix

接下来，当你的协作者再次从服务器上获取数据时，他们将得到一个新的远程分支 origin/serverfix：

$ git fetch origin remote: Counting objects: 20, done. remote: Compressing objects: 100% (14/14), done. remote: Total 15 (delta 5), reused 0 (delta 0) Unpacking objects: 100% (15/15), done.
From git@github.com:schacon/simplegit
* [new branch]  serverfix   -> origin/serverfix

值得注意的是，在 fetch 操作抓来新的远程分支之后，你仍然无法在本地编辑该远程仓库。换句话说，在本例中，你不会有一个新的 serverfix 分支，有的只是一个你无法移动的 origin/serverfix 指针。

如果要把该内容合并到当前分支，可以运行 git merge origin/serverfix。如果想要一份自己的 serverfix 来开发，可以在远程分支的基础上分化出一个新的分支来：

$ git checkout -b serverfix origin/serverfix
Branch serverfix set up to track remote branch refs/remotes/origin/serverfix.
Switched to a new branch "serverfix"

这会切换到新建的 serverfix 本地分支，其内容同远程分支 origin/serverfix 一致，你可以在里面继续开发 了。
3.3.7.2 跟踪分支

从远程分支检出的本地分支，称为跟踪分支(tracking branch)。跟踪分支是一种和远程分支有直接联系的本地分支。在跟踪分支里输入 git push，Git 会自行推断应该向哪个服务器的哪个分支推送数据。反过来，在这些分支里运行 git pull 会获取所有远程索引，并把它们的数据都合并到本地分支中来。

在克隆仓库时，Git 通常会自动创建一个 master 分支来跟踪 origin/master。这正是 git push 和 git pull 一开始就能正常工作的原因。当然，你可以随心所欲地设定为其它跟踪分支，比如 origin 上除了 master 之外的其它分支。刚才我们已经看到了这样的一个例子：git checkout -b [分支名] [远程名]/[分支名]。如果你有 1.6.2 以上版本的 Git，还可以用 –track 选项简化：

$ git checkout --track origin/serverfix
Branch serverfix set up to track remote branch refs/remotes/origin/serverfix.
Switched to a new branch "serverfix"

要为本地分支设定不同于远程分支的名字，只需在前个版本的命令里换个名字：

$ git checkout -b sf origin/serverfix
Branch sf set up to track remote branch refs/remotes/origin/serverfix.
Switched to a new branch "sf"

现在你的本地分支 sf 会自动向 origin/serverfix 推送和抓取数据了。
3.3.7.3 删除远程分支

如果不再需要某个远程分支了，比如搞定了某个特性并把它合并进了远程的 master 分支（或任何其他存放稳定代码的地方），可以用这个非常无厘头的语法来删除它：git push [远程名] :[分支名]。如果想在服务器上删除 serverfix 分支，运行下面的命令：

$ git push origin :serverfix
To git@github.com:schacon/simplegit.git
- [deleted] serverfix

3.8 小结

读到这里，你应该已经学会了如何创建分支并切换到新分支；在不同分支间转换；合并本地分支；把分支推送到共享服务器上，同世界分享；
*/

?>
