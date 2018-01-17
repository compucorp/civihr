To ensure that your changes adhere to coding standards before committing them you need to run **civilint** on staged files to ensure  that there is no code  violations before committing the changes.

 To make your life easier, This git pre-commit hook will do that for you every time you run (git commit) command.  

### Setup:

- Copy **bin/pre-commit** to civihr repo **.git/hooks/** inside your local civihr site.  

![selection_139](https://cloud.githubusercontent.com/assets/6275540/20575455/3cecb418-b1b1-11e6-93a0-53775439adbe.png)


### Usage : 

Suppose you  are working on a branch feature branch called **PCHR-501-sample-data-extension**  and suppose you have one modified and staged file and one modified but not staged file as you can see in the picture below :

![selection_138](https://cloud.githubusercontent.com/assets/6275540/20575411/12df05f4-b1b1-11e6-8201-6f381ac00206.png)

Then If you run :

```bash
git commit -m "Update something ..."
```

And in case the staged file contain some code standard violations then the following errors will appear showing you where the violations are and your code will not be committed :

....Add pic here...

Hence that you didn't get any warning from the not staged file because the pre-hook script will only check staged files .


Now You can go through these errors one by one and fix them , And after you are done you can commit your work :

```bash
git commit -m "Update something ..."
```

....Add pic here...