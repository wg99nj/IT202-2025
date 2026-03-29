# Project: Predicting Obesity Levels Using Machine Learning
### Group 7: Wilfer Gelabert & Sabeeh Qureshi
## Course: AI-Driven Text Analytics IS-392
## Step 3: Project Code Review

── HOW TO RUN IT ──────────────────────────────────────────
1. Open the notebook in  either Google Colab or Jupyter Notebook
2. Run all cells in order from top to bottom
3. The dataset is loaded automatically via ucimlrepo (no CSV is needed)
   - Cell 1 installs the package: !pip install ucimlrepo
   - Cell 2 fetches the dataset directly from UCI

── WHAT THE CODE DOES ──────────────────────────────────
- Loads the UCI Obesity Levels dataset (2,111 rows, 17 features)
- Performs EDA: class distribution, correlation heatmap, box plots
- Preprocesses data: label encoding, standard scaling, 80/20 split
- Trains and evaluates two models:
    * Gaussian Naive Bayes (baseline) — Macro F1: 0.57
    * SVM with GridSearchCV tuning  — Macro F1: 0.97
- Outputs confusion matrices and a model comparison bar chart

── KNOWN ISSUES/NEXT STEPS ───────────────────────────
- SVM GridSearch may take 1-2 minutes to run
- Next steps: add ROC-AUC curves per class, write final
  analysis and conclusions for Step 4
